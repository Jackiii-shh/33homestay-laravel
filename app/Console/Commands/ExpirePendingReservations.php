<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Console\Command;

class ExpirePendingReservations extends Command
{
    protected $signature = 'reservations:expire-pending';

    protected $description = 'Batalkan otomatis reservasi "Menunggu DP" yang melewati batas waktu pembayaran';

    public function handle(ReservationService $service): int
    {
        $expired = Reservation::query()
            ->where('status', Reservation::STATUS_PENDING)
            ->whereNotNull('dp_deadline')
            ->where('dp_deadline', '<', now())
            ->get();

        $count = 0;
        foreach ($expired as $reservation) {
            if ($service->autoExpire($reservation)) {
                $count++;
            }
        }

        $this->info("Reservasi dibatalkan otomatis: {$count}");

        return self::SUCCESS;
    }
}
