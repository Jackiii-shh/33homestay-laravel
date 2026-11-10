<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Services\AvailabilityService;

class DashboardController extends Controller
{
    public function index(AvailabilityService $availability)
    {
        $today = today();
        $with = ['room.type'];

        $arrivals = Reservation::with($with)->withSum('payments', 'amount')
            ->where('status', Reservation::STATUS_CONFIRMED)
            ->where('check_in', '<=', $today->toDateString())
            ->orderBy('check_in')->get();

        $inHouse = Reservation::with($with)->withSum('payments', 'amount')
            ->where('status', Reservation::STATUS_CHECKED_IN)
            ->orderBy('check_out')->get();

        $pending = Reservation::with($with)
            ->where('status', Reservation::STATUS_PENDING)
            ->orderBy('dp_deadline')->get();

        $otaBlock = Reservation::with($with)
            ->whereIn('status', Reservation::ACTIVE_STATUSES)
            ->whereNotIn('channel', Reservation::OTA_CHANNELS)
            ->whereNull('ota_blocked_at')
            ->where('check_out', '>=', $today->toDateString())
            ->orderBy('check_in')->get();

        $otaUnblock = Reservation::with($with)
            ->whereIn('status', [Reservation::STATUS_CANCELLED, Reservation::STATUS_NO_SHOW])
            ->whereNotNull('ota_blocked_at')
            ->whereNull('ota_unblocked_at')
            ->where('check_out', '>=', $today->toDateString())
            ->orderBy('check_in')->get();

        $rooms = $availability->roomStatusOn($today);

        $stats = [
            'arrivals_today' => $arrivals->filter(fn ($r) => $r->check_in->isSameDay($today))->count(),
            'in_house' => $inHouse->count(),
            'departures_today' => $inHouse->filter(fn ($r) => $r->check_out->lte($today))->count(),
            'pending' => $pending->count(),
            'occupied' => $rooms->whereIn('state', ['terisi', 'dibooking'])->count(),
            'total_rooms' => $rooms->count(),
        ];

        return view('staff.dashboard', compact(
            'arrivals', 'inHouse', 'pending', 'otaBlock', 'otaUnblock', 'rooms', 'stats'
        ));
    }
}
