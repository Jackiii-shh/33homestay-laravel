<?php

namespace Database\Seeders;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use App\Services\AvailabilityService;
use App\Services\PaymentService;
use App\Services\ReservationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Data CONTOH (dummy) agar dashboard & kalender terisi. Seluruhnya dibuat lewat service
 * yang sama dengan aplikasi, sehingga tidak ada double booking dalam data demo.
 */
class DemoDataSeeder extends Seeder
{
    private array $names = [
        'Budi Santoso', 'Siti Rahma', 'Andi Pratama', 'Dewi Lestari', 'Rizky Maulana', 'Putri Ayu',
        'Fajar Nugroho', 'Nadia Kusuma', 'Agus Salim', 'Maya Sari', 'Hendra Wijaya', 'Lina Marlina',
        'Eko Prasetyo', 'Rina Wulandari', 'Dimas Aditya', 'Citra Dewi', 'Yoga Permana', 'Sari Indah',
        'Bagas Saputra', 'Intan Permata', 'Rudi Hartono', 'Wulan Sari', 'Joko Susilo', 'Tika Amelia',
    ];

    private array $cities = ['Jakarta', 'Bekasi', 'Depok', 'Tangerang', 'Bogor', 'Jakarta', 'Bekasi'];

    /** @var array<int, array<string,bool>> room_id => [tanggal => true] untuk data historis */
    private array $occupied = [];

    public function run(): void
    {
        if (Reservation::query()->exists()) {
            $this->command?->warn('Data reservasi sudah ada; seeder demo dilewati.');

            return;
        }

        mt_srand(33);
        $staff = User::where('role', User::ROLE_PENGELOLA)->firstOrFail();
        $rs = app(ReservationService::class);
        $ps = app(PaymentService::class);
        $availability = app(AvailabilityService::class);
        $rooms = Room::with('type')->orderBy('code')->get();

        $this->seedHistory($rooms, $rs, $ps, $staff);
        $this->seedCurrent($rooms->keyBy('code'), $rs, $ps, $availability, $staff);
    }

    // ---------------------------------------------------------------- historis
    private function seedHistory($rooms, ReservationService $rs, PaymentService $ps, User $staff): void
    {
        $channels = ['whatsapp', 'whatsapp', 'whatsapp', 'whatsapp', 'whatsapp', 'whatsapp', 'agoda', 'agoda', 'tiketcom', 'walk_in', 'walk_in'];

        for ($offset = -75; $offset <= -3; $offset++) {
            foreach ($rooms as $room) {
                $in = today()->addDays($offset);
                // akhir pekan lebih ramai
                $chance = in_array($in->dayOfWeek, [Carbon::FRIDAY, Carbon::SATURDAY], true) ? 70 : 35;
                if (mt_rand(1, 100) > $chance) {
                    continue;
                }

                $nights = mt_rand(1, 3);
                $out = $in->copy()->addDays($nights);
                if ($out->gt(today()->subDay()) || $this->isOccupied($room->id, $in, $out)) {
                    continue;
                }

                $channel = $channels[array_rand($channels)];
                $reservation = $rs->create([
                    'room_id' => $room->id,
                    'guest_name' => $this->names[array_rand($this->names)],
                    'guest_city' => $this->cities[array_rand($this->cities)],
                    'guest_phone' => '08120000'.mt_rand(1000, 9999),
                    'guest_count' => mt_rand(1, $room->type->capacity),
                    'check_in' => $in->toDateString(),
                    'check_out' => $out->toDateString(),
                    'channel' => $channel,
                ], $staff);

                $roll = mt_rand(1, 100);
                if ($channel !== 'agoda' && $channel !== 'tiketcom') {
                    $dp = $reservation->minimumDp();
                    $ps->record($reservation, ['amount' => $dp, 'method' => 'transfer', 'paid_at' => $in->copy()->subDays(mt_rand(1, 4))->setTime(mt_rand(9, 20), 0)], $staff);

                    if ($roll <= 7) {            // no-show: DP hangus
                        $reservation->update(['status' => Reservation::STATUS_NO_SHOW, 'dp_forfeited' => true, 'cancelled_at' => $in->copy()->setTime(22, 5)]);

                        continue;
                    }
                    if ($roll <= 13) {           // batal, refund penuh
                        $rs->cancel($reservation->fresh(), 'Tamu membatalkan (jadwal berubah)', $dp, $staff);
                        $reservation->update(['cancelled_at' => $in->copy()->subDays(2)]);

                        continue;
                    }

                    $remaining = $reservation->total_price - $dp;
                    if ($remaining > 0) {
                        $ps->record($reservation->fresh(), ['amount' => $remaining, 'method' => mt_rand(0, 1) ? 'cash' : 'transfer', 'paid_at' => $in->copy()->setTime(14, mt_rand(0, 40))], $staff);
                    }
                } else {
                    $reservation->payments()->update(['paid_at' => $in->copy()->subDays(mt_rand(3, 10))]);
                }

                $reservation->update([
                    'status' => Reservation::STATUS_CHECKED_OUT,
                    'checked_in_at' => $in->copy()->setTime(14, mt_rand(0, 50)),
                    'checked_out_at' => $out->copy()->setTime(11, mt_rand(0, 50)),
                ]);

                $this->markOccupied($room->id, $in, $out);
            }
        }
    }

    // --------------------------------------------------------- hari ini & ke depan
    private function seedCurrent($rooms, ReservationService $rs, PaymentService $ps, AvailabilityService $availability, User $staff): void
    {
        $make = function (string $code, int $inOffset, int $outOffset, string $channel, string $name, int $guests = 2) use ($rooms, $rs, $availability, $staff) {
            $room = $rooms[$code];
            $in = today()->addDays($inOffset)->toDateString();
            $out = today()->addDays($outOffset)->toDateString();
            if ($availability->conflictFor($room->id, $in, $out)) {
                return null;
            }

            return $rs->create([
                'room_id' => $room->id, 'guest_name' => $name, 'guest_city' => $this->cities[array_rand($this->cities)],
                'guest_phone' => '08120000'.mt_rand(1000, 9999), 'guest_count' => min($guests, $room->type->capacity),
                'check_in' => $in, 'check_out' => $out, 'channel' => $channel,
            ], $staff);
        };
        $pay = fn (?Reservation $r, ?int $amount = null) => $r ? $ps->record($r, [
            'amount' => $amount ?? $r->total_price, 'method' => 'transfer', 'paid_at' => now()->subDays(2),
        ], $staff) : null;

        // Sedang menginap (lunas)
        if ($r = $make('AC-1', -1, 2, 'whatsapp', 'Hendra Wijaya')) {
            $pay($r);
            $r->update(['status' => Reservation::STATUS_CHECKED_IN, 'checked_in_at' => now()->subDay()->setTime(14, 10), 'ota_blocked_at' => now()->subDays(3)]);
        }
        // Check-out hari ini (lunas)
        if ($r = $make('MZN-1', -2, 0, 'whatsapp', 'Keluarga Rahman', 4)) {
            $pay($r);
            $r->update(['status' => Reservation::STATUS_CHECKED_IN, 'checked_in_at' => now()->subDays(2)->setTime(15, 0), 'ota_blocked_at' => now()->subDays(4)]);
        }
        // Tamu sudah check-out hari ini -> kamar sedang dibersihkan
        if ($r = $make('KIPAS-1', -2, 0, 'walk_in', 'Lina Marlina')) {
            $pay($r);
            $r->update(['status' => Reservation::STATUS_CHECKED_OUT, 'checked_in_at' => now()->subDays(2)->setTime(14, 0), 'checked_out_at' => now()->setTime(9, 30), 'ota_blocked_at' => now()->subDays(4)]);
            $r->room->update(['status' => Room::STATUS_CLEANING]);
        }
        // Tiba hari ini: DP saja (perlu pelunasan saat check-in)
        if ($r = $make('AC-2', 0, 1, 'whatsapp', 'Dewi Lestari')) {
            $pay($r, $r->minimumDp());
            $r->update(['ota_blocked_at' => now()->subDay()]);
        }
        // Tiba hari ini: sudah lunas, siap check-in
        if ($r = $make('TERA-1', 0, 2, 'whatsapp', 'Fajar Nugroho', 4)) {
            $pay($r);
            $r->update(['ota_blocked_at' => now()->subDays(2)]);
        }
        // Seharusnya tiba kemarin, tidak datang -> perlu tindakan no-show
        if ($r = $make('AC-3', -1, 0, 'whatsapp', 'Agus Salim')) {
            $pay($r, $r->minimumDp());
            $r->update(['ota_blocked_at' => now()->subDays(3)]);
        }
        // Menunggu DP (belum diblokir di OTA -> muncul di checklist OTA)
        $make('AC-3', 1, 3, 'whatsapp', 'Rizky Maulana');

        // Akhir pekan depan
        $friday = today()->next(Carbon::FRIDAY);
        $fo = (int) round(today()->diffInDays($friday)); // Carbon 3: positif bila $friday di masa depan
        if ($r = $make('MZN-1', $fo, $fo + 2, 'whatsapp', 'Keluarga Santoso', 4)) {
            $pay($r, $r->minimumDp());
            $r->update(['ota_blocked_at' => now()]);
        }
        if ($r = $make('AC-2', $fo, $fo + 2, 'whatsapp', 'Nadia Kusuma')) {
            $pay($r);
            $r->update(['ota_blocked_at' => now()]);
        }
        $make('KIPAS-1', $fo, $fo + 2, 'whatsapp', 'Bagas Saputra');
        if ($r = $make('TERA-1', $fo, $fo + 2, 'whatsapp', 'Rombongan Wijaya', 4)) {
            $pay($r, $r->minimumDp());
            $r->update(['ota_blocked_at' => now()]);
        }

        // Dari OTA (otomatis terkonfirmasi & terbayar via OTA)
        $make('KIPAS-1', 4, 6, 'agoda', 'Tamu Agoda');
        $make('AC-2', 3, 5, 'tiketcom', 'Tamu Tiket.com');

        // Dibatalkan setelah sempat diblokir di OTA -> perlu dibuka lagi di OTA
        if ($r = $make('AC-1', 6, 8, 'whatsapp', 'Wulan Sari')) {
            $pay($r, $r->minimumDp());
            $r->update(['ota_blocked_at' => now()->subDay()]);
            $rs->cancel($r->fresh(), 'Tamu membatalkan', $r->minimumDp(), $staff);
        }

        // Contoh percobaan double booking yang DITOLAK sistem (tercatat di audit & dashboard Owner)
        try {
            $rs->create([
                'room_id' => $rooms['AC-1']->id, 'guest_name' => 'Percobaan Bentrok', 'guest_count' => 2,
                'check_in' => today()->toDateString(), 'check_out' => today()->addDay()->toDateString(), 'channel' => 'whatsapp',
            ], $staff);
        } catch (\App\Exceptions\RoomConflictException) {
            // diharapkan: ditolak
        }
    }

    private function isOccupied(int $roomId, Carbon $in, Carbon $out): bool
    {
        for ($d = $in->copy(); $d->lt($out); $d->addDay()) {
            if (! empty($this->occupied[$roomId][$d->toDateString()])) {
                return true;
            }
        }

        return false;
    }

    private function markOccupied(int $roomId, Carbon $in, Carbon $out): void
    {
        for ($d = $in->copy(); $d->lt($out); $d->addDay()) {
            $this->occupied[$roomId][$d->toDateString()] = true;
        }
    }
}
