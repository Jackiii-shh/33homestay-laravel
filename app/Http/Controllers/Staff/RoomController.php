<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Reservation;
use App\Models\Room;
use App\Services\AvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RoomController extends Controller
{
    public function index(AvailabilityService $availability)
    {
        return view('staff.rooms.index', ['rooms' => $availability->roomStatusOn(today())]);
    }

    public function updateStatus(Request $request, Room $room)
    {
        $data = $request->validate(['status' => ['required', Rule::in(array_keys(Room::STATUSES))]]);

        if ($data['status'] === Room::STATUS_MAINTENANCE) {
            $upcoming = Reservation::query()
                ->whereIn('status', Reservation::ACTIVE_STATUSES)
                ->where('room_id', $room->id)
                ->where('check_out', '>', today()->toDateString())
                ->pluck('code');

            if ($upcoming->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'status' => 'Kamar '.$room->code.' masih punya reservasi aktif ('.$upcoming->implode(', ').'). Pindahkan/batalkan dahulu sebelum maintenance.',
                ]);
            }
        }

        $old = $room->status;
        $room->update(['status' => $data['status']]);

        ActivityLog::record(
            'room_status_changed', $room,
            sprintf('Status kamar %s: %s -> %s.', $room->code, Room::STATUSES[$old] ?? $old, Room::STATUSES[$data['status']]),
        );

        return back()->with('success', 'Status kamar '.$room->code.' diperbarui.');
    }
}
