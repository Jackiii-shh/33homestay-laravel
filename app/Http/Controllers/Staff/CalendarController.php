<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class CalendarController extends Controller
{
    /** Kalender ketersediaan: baris = unit kamar, kolom = hari (satu sumber data untuk Owner & Pengelola). */
    public function index(Request $request)
    {
        try {
            $month = Carbon::createFromFormat('Y-m-d', ($request->query('bulan') ?: now()->format('Y-m')).'-01')->startOfDay();
        } catch (\Throwable) {
            $month = now()->startOfMonth();
        }

        $start = $month->copy()->startOfMonth();
        $days = $start->daysInMonth;
        $endExclusive = $start->copy()->addDays($days);

        $reservations = Reservation::query()
            ->whereIn('status', Reservation::ACTIVE_STATUSES)
            ->where('check_in', '<', $endExclusive->toDateString())
            ->where('check_out', '>', $start->toDateString())
            ->get()
            ->groupBy('room_id');

        $rooms = Room::with('type')->where('is_active', true)->orderBy('code')->get();

        $rows = $rooms->map(function (Room $room) use ($reservations, $start, $days, $endExclusive) {
            $items = $reservations->get($room->id, collect());
            $segments = [];
            $d = 0;

            while ($d < $days) {
                $date = $start->copy()->addDays($d);
                $res = $items->first(fn (Reservation $r) => $r->check_in->lte($date) && $r->check_out->gt($date));

                if (! $res) {
                    $segments[] = ['res' => null, 'span' => 1, 'date' => $date];
                    $d++;

                    continue;
                }

                $end = $res->check_out->lt($endExclusive) ? $res->check_out : $endExclusive;
                $span = max(1, (int) round(($end->getTimestamp() - $date->getTimestamp()) / 86400));
                $segments[] = ['res' => $res, 'span' => $span, 'date' => $date];
                $d += $span;
            }

            return ['room' => $room, 'segments' => $segments];
        });

        return view('staff.calendar', [
            'month' => $start,
            'days' => $days,
            'rows' => $rows,
            'prev' => $start->copy()->subMonth()->format('Y-m'),
            'next' => $start->copy()->addMonth()->format('Y-m'),
        ]);
    }
}
