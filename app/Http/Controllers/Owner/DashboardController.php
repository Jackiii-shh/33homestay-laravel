<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\AvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request, AvailabilityService $availability)
    {
        try {
            $month = Carbon::createFromFormat('Y-m-d', ($request->query('bulan') ?: now()->format('Y-m')).'-01')->startOfDay();
        } catch (\Throwable) {
            $month = now()->startOfMonth();
        }

        $start = $month->copy()->startOfMonth();
        $monthEnd = $start->copy()->endOfMonth()->startOfDay();
        $effectiveEnd = $monthEnd->gt(today()) ? today() : $monthEnd;
        $elapsedDays = $effectiveEnd->lt($start) ? 0 : AvailabilityService::nights($start, $effectiveEnd) + 1;

        $payRange = [$start->copy()->startOfDay(), $monthEnd->copy()->endOfDay()];
        $dateRange = [$start->toDateString(), $monthEnd->toDateString()];

        // --- okupansi ---
        $units = Room::where('is_active', true)->count();
        $sold = $elapsedDays > 0 ? $availability->soldRoomNights($start, $effectiveEnd) : 0;
        $occupancy = $units * $elapsedDays > 0 ? round($sold / ($units * $elapsedDays) * 100, 1) : 0;

        $byType = RoomType::where('is_active', true)->orderBy('sort_order')->get()->map(function (RoomType $t) use ($availability, $start, $effectiveEnd, $elapsedDays) {
            $tUnits = Room::where('is_active', true)->where('room_type_id', $t->id)->count();
            $tSold = $elapsedDays > 0 ? $availability->soldRoomNights($start, $effectiveEnd, $t->id) : 0;

            return [
                'name' => $t->name,
                'units' => $tUnits,
                'sold' => $tSold,
                'occupancy' => $tUnits * $elapsedDays > 0 ? round($tSold / ($tUnits * $elapsedDays) * 100, 1) : 0,
            ];
        });

        // --- pendapatan ---
        $revenue = (int) Payment::whereBetween('paid_at', $payRange)->sum('amount');
        $revenueByMethod = Payment::whereBetween('paid_at', $payRange)
            ->selectRaw('method, SUM(amount) as total')->groupBy('method')->pluck('total', 'method');
        $daily = Payment::whereBetween('paid_at', $payRange)
            ->selectRaw('DATE(paid_at) as d, SUM(amount) as total')->groupBy('d')->pluck('total', 'd');

        $chartLabels = [];
        $chartValues = [];
        for ($i = 0; $i < $start->daysInMonth; $i++) {
            $day = $start->copy()->addDays($i);
            $chartLabels[] = $day->format('d');
            $chartValues[] = (int) ($daily[$day->toDateString()] ?? 0);
        }

        $monthly = [];
        for ($i = 5; $i >= 0; $i--) {
            $m = $start->copy()->subMonths($i);
            $monthly[] = [
                'label' => $m->translatedFormat('M Y'),
                'total' => (int) Payment::whereBetween('paid_at', [$m->copy()->startOfMonth(), $m->copy()->endOfMonth()])->sum('amount'),
            ];
        }

        // --- reservasi ---
        $statusCounts = Reservation::whereBetween('check_in', $dateRange)
            ->selectRaw('status, COUNT(*) as c')->groupBy('status')->pluck('c', 'status');
        $channelCounts = Reservation::whereBetween('check_in', $dateRange)
            ->selectRaw('channel, COUNT(*) as c')->groupBy('channel')->pluck('c', 'channel');

        $blockedMonth = ActivityLog::where('action', 'booking_conflict_blocked')
            ->whereBetween('created_at', $payRange)->count();
        $blockedTotal = ActivityLog::where('action', 'booking_conflict_blocked')->count();

        // --- piutang (sisa tagihan tamu yang sudah/akan tiba) ---
        $outstanding = Reservation::with('room')->withSum('payments', 'amount')
            ->whereIn('status', [Reservation::STATUS_CONFIRMED, Reservation::STATUS_CHECKED_IN])
            ->where('check_in', '<=', today()->toDateString())
            ->get()->filter(fn (Reservation $r) => $r->balance > 0)->values();

        $upcoming = Reservation::with('room.type')->withSum('payments', 'amount')
            ->whereIn('status', [Reservation::STATUS_PENDING, Reservation::STATUS_CONFIRMED])
            ->whereBetween('check_in', [today()->toDateString(), today()->addDays(7)->toDateString()])
            ->orderBy('check_in')->get();

        $rooms = $availability->roomStatusOn(today());
        $recentLogs = ActivityLog::with('user')->latest()->limit(8)->get();

        return view('owner.dashboard', [
            'month' => $start,
            'prev' => $start->copy()->subMonth()->format('Y-m'),
            'next' => $start->copy()->addMonth()->format('Y-m'),
            'units' => $units,
            'sold' => $sold,
            'occupancy' => $occupancy,
            'byType' => $byType,
            'revenue' => $revenue,
            'revenueByMethod' => $revenueByMethod,
            'chartLabels' => $chartLabels,
            'chartValues' => $chartValues,
            'monthly' => $monthly,
            'statusCounts' => $statusCounts,
            'channelCounts' => $channelCounts,
            'blockedMonth' => $blockedMonth,
            'blockedTotal' => $blockedTotal,
            'outstanding' => $outstanding,
            'upcoming' => $upcoming,
            'rooms' => $rooms,
            'recentLogs' => $recentLogs,
        ]);
    }
}
