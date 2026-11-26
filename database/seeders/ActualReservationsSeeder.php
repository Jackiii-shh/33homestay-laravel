<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ActualReservationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Kebijakan Impor Data (Tugas 12):
     * 1. HANYA memasukkan reservasi unit inti (unit_scope = 'inti') dengan kode kamar valid (K1 - K5).
     * 2. Baris TH1 (Teras House) dan Unassigned dikecualikan dari seeder database.
     * 3. Baris dengan date_unresolved dilewati karena tanggal tidak valid untuk stempel database.
     * 4. Menggunakan identitas teranonimkan (Tamu-XXXX) dan nomor kontak aman (08XXXXXXXXXX).
     * 5. Kompatibel lintas database (MySQL & SQLite) menggunakan Schema::disableForeignKeyConstraints().
     */
    public function run(): void
    {
        $csvPath = base_path('data-science/1_data_cleaning/clean_reservations.csv');
        if (!file_exists($csvPath)) {
            $this->command?->warn("File clean_reservations.csv tidak ditemukan di {$csvPath}");
            return;
        }

        $rooms = Room::all()->keyBy('code');
        $staff = User::where('role', User::ROLE_PENGELOLA)->first() ?? User::first();

        $handle = fopen($csvPath, 'r');
        if (!$handle) {
            return;
        }

        $header = fgetcsv($handle);
        $headerMap = array_flip($header);

        $this->command?->info('Mengimpor reservasi aktual terverifikasi unit inti (K1-K5) ke database...');

        // Bersihkan data lama dengan metode kompatibel SQLite & MySQL
        Schema::disableForeignKeyConstraints();
        DB::table('payments')->truncate();
        DB::table('invoices')->truncate();
        DB::table('activity_logs')->where('subject_type', Reservation::class)->delete();
        DB::table('reservations')->truncate();
        Schema::enableForeignKeyConstraints();

        DB::beginTransaction();
        try {
            $count = 0;
            $skippedScope = 0;
            $skippedDate = 0;

            while (($row = fgetcsv($handle)) !== false) {
                if (empty($row) || count($row) < count($headerMap)) {
                    continue;
                }

                $unitScope = $row[$headerMap['unit_scope']] ?? '';
                $roomCode = trim($row[$headerMap['room_code']] ?? '');
                $dateUnresolved = filter_var($row[$headerMap['date_unresolved']] ?? false, FILTER_VALIDATE_BOOLEAN);

                // Filter Task 12: Hanya unit inti dengan room_code K1-K5
                if ($unitScope !== 'inti' || !in_array($roomCode, ['K1', 'K2', 'K3', 'K4', 'K5'], true)) {
                    $skippedScope++;
                    continue;
                }

                // Abaikan jika tanggal tidak terselesaikan
                $checkIn = $row[$headerMap['check_in']] ?? '';
                $checkOut = $row[$headerMap['check_out']] ?? '';
                if ($dateUnresolved || empty($checkIn) || empty($checkOut)) {
                    $skippedDate++;
                    continue;
                }

                $room = $rooms->get($roomCode);
                if (!$room) {
                    $skippedScope++;
                    continue;
                }

                $code = $row[$headerMap['reservation_code']];
                $guestName = $row[$headerMap['guest_name']];
                $guestCity = $row[$headerMap['guest_city']] ?? 'Serang';
                $guestPhone = $row[$headerMap['guest_phone']] ?? '08XXXXXXXXXX';
                $pricePerNight = (int) ($row[$headerMap['price_per_night']] ?? $room->roomType->price_per_night);
                $nights = (int) ($row[$headerMap['nights']] ?? 1);
                $totalPrice = (int) $row[$headerMap['total_price']];
                $statusRaw = $row[$headerMap['status']] ?? 'PAID_OFF';
                $channelRaw = strtolower($row[$headerMap['channel']] ?? '');
                $otaName = strtolower($row[$headerMap['ota_name']] ?? '');
                $rentalType = ($row[$headerMap['rental_type']] ?? '') === 'transit' ? 'transit' : 'harian';
                $notes = $row[$headerMap['notes']] ?? '';

                // Status mapping
                $status = ($statusRaw === 'CANCELLED') ? Reservation::STATUS_CANCELLED : Reservation::STATUS_CHECKED_OUT;

                // Channel mapping
                if (str_contains($otaName, 'tiket') || str_contains($channelRaw, 'tiket')) {
                    $channel = 'tiketcom';
                } elseif (str_contains($otaName, 'traveloka') || str_contains($channelRaw, 'traveloka')) {
                    $channel = 'traveloka';
                } elseif (str_contains($otaName, 'agoda') || str_contains($channelRaw, 'agoda')) {
                    $channel = 'agoda';
                } elseif ($channelRaw === 'ota') {
                    $channel = 'agoda'; // default OTA
                } else {
                    $channel = 'walk_in';
                }

                // Cash flow status
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
                    'guest_count' => $room->roomType->capacity ?? 2,
                    'check_in' => $checkIn,
                    'check_out' => $checkOut,
                    'nights' => $nights,
                    'price_per_night' => $pricePerNight,
                    'total_price' => $totalPrice,
                    'status' => $status,
                    'channel' => $channel,
                    'rental_type' => $rentalType,
                    'cash_flow_status' => $cashFlowStatus,
                    'notes' => !empty($notes) ? $notes : null,
                    'checked_in_at' => $status !== Reservation::STATUS_CANCELLED ? $dtIn->copy()->setTime(14, 0) : null,
                    'checked_out_at' => $status !== Reservation::STATUS_CANCELLED ? $dtOut->copy()->setTime(12, 0) : null,
                    'cancelled_at' => $status === Reservation::STATUS_CANCELLED ? $dtIn->copy()->subDays(2) : null,
                    'created_by' => $staff?->id,
                    'created_at' => $dtIn->copy()->subDays(3)->setTime(10, 0),
                    'updated_at' => $dtOut->copy()->setTime(12, 30),
                ]);

                // Buat data pembayaran lunas untuk checked_out
                if ($status !== Reservation::STATUS_CANCELLED && $totalPrice > 0) {
                    $method = in_array($channel, Reservation::OTA_CHANNELS, true) ? 'ota' : 'cash';

                    DB::table('payments')->insert([
                        'reservation_id' => $resId,
                        'type' => 'full',
                        'amount' => $totalPrice,
                        'method' => $method,
                        'paid_at' => $dtIn->copy()->setTime(14, 15),
                        'note' => 'Pembayaran lunas buku kas fisik owner',
                        'received_by' => $staff?->id,
                        'created_at' => $dtIn->copy()->setTime(14, 15),
                        'updated_at' => $dtIn->copy()->setTime(14, 15),
                    ]);
                }

                $count++;
            }

            DB::commit();
            fclose($handle);

            $this->command?->info("Sukses mengimpor {$count} data reservasi unit inti (K1-K5) ke database!");
            $this->command?->info("Dilewati: {$skippedScope} baris di luar scope (TH1/Unassigned), {$skippedDate} baris tanpa tanggal valid.");
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);
            throw $e;
        }
    }
}
