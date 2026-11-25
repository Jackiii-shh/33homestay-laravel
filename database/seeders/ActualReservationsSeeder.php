<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ActualReservationsSeeder extends Seeder
{
    public function run(): void
    {
        $csvPath = base_path('data-science/1_data_cleaning/clean_reservations.csv');
        if (!file_exists($csvPath)) {
            $this->command?->warn("File clean_reservations.csv tidak ditemukan di {$csvPath}");
            return;
        }

        $rooms = Room::all()->keyBy('code');
        $defaultRoom = $rooms->get('K1') ?? Room::first();
        $staff = User::where('role', User::ROLE_PENGELOLA)->first() ?? User::first();

        $handle = fopen($csvPath, 'r');
        if (!$handle) {
            return;
        }

        // Header
        $header = fgetcsv($handle);
        $headerMap = array_flip($header);

        $this->command?->info('Mengimpor data reservasi aktual dari clean_reservations.csv (~1.097 baris bersih)...');

        // Hapus reservasi dan pembayaran lama jika ada agar bersih (DDL dilakukan di luar transaksi)
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('payments')->truncate();
        DB::table('invoices')->truncate();
        DB::table('activity_logs')->where('subject_type', Reservation::class)->delete();
        DB::table('reservations')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        DB::beginTransaction();
        try {
            $count = 0;
            while (($row = fgetcsv($handle)) !== false) {
                if (empty($row) || count($row) < count($headerMap)) {
                    continue;
                }

                $code = $row[$headerMap['reservation_code']];
                $guestName = $row[$headerMap['guest_name']];
                $guestCity = $row[$headerMap['guest_city']] ?? 'Serang';
                $guestPhone = $row[$headerMap['guest_phone']] ?? '081234567890';
                $roomCode = $row[$headerMap['room_code']];
                $pricePerNight = (int) $row[$headerMap['price_per_night']];
                $checkIn = $row[$headerMap['check_in']];
                $checkOut = $row[$headerMap['check_out']];
                $nights = (int) $row[$headerMap['nights']];
                $totalPrice = (int) $row[$headerMap['total_price']];
                $guestCount = (int) ($row[$headerMap['guest_count']] ?? 2);
                $statusRaw = $row[$headerMap['status']];
                $channelRaw = strtolower($row[$headerMap['channel']] ?? 'whatsapp');
                $payMethodRaw = strtolower($row[$headerMap['payment_method']] ?? 'transfer');
                $rentalType = ($row[$headerMap['rental_type']] ?? '') === 'transit' ? 'transit' : 'harian';
                $notes = $row[$headerMap['notes']] ?? '-';

                $room = $rooms->get($roomCode, $defaultRoom);

                // Mapping Status
                $status = ($statusRaw === 'CANCELLED') ? Reservation::STATUS_CANCELLED : Reservation::STATUS_CHECKED_OUT;

                // Mapping Channel
                if (str_contains($channelRaw, 'tiket')) {
                    $channel = 'tiketcom';
                } elseif (str_contains($channelRaw, 'agoda')) {
                    $channel = 'agoda';
                } elseif (str_contains($channelRaw, 'traveloka')) {
                    $channel = 'traveloka';
                } elseif (str_contains($channelRaw, 'walk')) {
                    $channel = 'walk_in';
                } else {
                    $channel = 'whatsapp';
                }

                // Mapping Cash Flow Status
                $cashFlowStatus = str_contains(strtolower($notes), 'pengelola')
                    ? 'uang_di_pengelola'
                    : 'disetor_owner';

                $dtIn = Carbon::parse($checkIn);
                $dtOut = Carbon::parse($checkOut);

                $resId = DB::table('reservations')->insertGetId([
                    'code' => $code,
                    'room_id' => $room->id,
                    'guest_name' => $guestName,
                    'guest_city' => $guestCity,
                    'guest_phone' => $guestPhone,
                    'guest_count' => max(1, $guestCount),
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'nights' => $nights,
                    'price_per_night' => $pricePerNight,
                    'total_price' => $totalPrice,
                    'status' => $status,
                    'channel' => $channel,
                    'rental_type' => $rentalType,
                    'cash_flow_status' => $cashFlowStatus,
                    'notes' => $notes,
                    'checked_in_at' => $status !== Reservation::STATUS_CANCELLED ? $dtIn->copy()->setTime(14, 0) : null,
                    'checked_out_at' => $status !== Reservation::STATUS_CANCELLED ? $dtOut->copy()->setTime(12, 0) : null,
                    'cancelled_at' => $status === Reservation::STATUS_CANCELLED ? $dtIn->copy()->subDays(2) : null,
                    'created_by' => $staff?->id,
                    'created_at' => $dtIn->copy()->subDays(3)->setTime(10, 0),
                    'updated_at' => $dtOut->copy()->setTime(12, 30),
                ]);

                // Buat data pembayaran untuk yang lunas / bukan cancel
                if ($status !== Reservation::STATUS_CANCELLED && $totalPrice > 0) {
                    $method = 'transfer';
                    if (str_contains($payMethodRaw, 'cash') || str_contains($payMethodRaw, 'tunai')) {
                        $method = 'cash';
                    } elseif (str_contains($payMethodRaw, 'qris')) {
                        $method = 'qris';
                    } elseif (str_contains($payMethodRaw, 'ota') || str_contains($channelRaw, 'tiket') || str_contains($channelRaw, 'agoda')) {
                        $method = 'ota';
                    }

                    DB::table('payments')->insert([
                        'reservation_id' => $resId,
                        'type' => 'full',
                        'amount' => $totalPrice,
                        'method' => $method,
                        'paid_at' => $dtIn->copy()->setTime(14, 15),
                        'note' => 'Pembayaran lunas sesuai buku kas aktual owner',
                        'received_by' => $staff?->id,
                        'created_at' => $dtIn->copy()->setTime(14, 15),
                        'updated_at' => $dtIn->copy()->setTime(14, 15),
                    ]);
                }

                $count++;
            }

            DB::commit();
            fclose($handle);

            $this->command?->info("Sukses mengimpor {$count} data reservasi aktual ke MySQL database!");
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);
            throw $e;
        }
    }
}
