<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    public function index()
    {
        return view('owner.rooms.index', [
            'rooms' => Room::with('type')->withCount('reservations')->orderBy('code')->get(),
        ]);
    }

    public function create()
    {
        return view('owner.rooms.form', ['room' => new Room(['status' => Room::STATUS_READY, 'is_active' => true]), 'types' => RoomType::orderBy('sort_order')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'room_type_id' => ['required', 'integer', 'exists:room_types,id'],
            'code' => ['required', 'string', 'max:30', 'unique:rooms,code'],
            'status' => ['required', Rule::in(array_keys(Room::STATUSES))],
            'notes' => ['nullable', 'string', 'max:300'],
        ]);

        $room = Room::create($data + ['is_active' => $request->boolean('is_active', true)]);
        ActivityLog::record('master_data_changed', $room, 'Unit kamar '.$room->code.' ditambahkan.');

        return redirect()->route('owner.rooms.index')->with('success', 'Unit kamar ditambahkan.');
    }

    public function edit(Room $room)
    {
        return view('owner.rooms.form', ['room' => $room, 'types' => RoomType::orderBy('sort_order')->get()]);
    }

    public function update(Request $request, Room $room)
    {
        $data = $request->validate([
            'room_type_id' => ['required', 'integer', 'exists:room_types,id'],
            'code' => ['required', 'string', 'max:30', Rule::unique('rooms', 'code')->ignore($room->id)],
            'status' => ['required', Rule::in(array_keys(Room::STATUSES))],
            'notes' => ['nullable', 'string', 'max:300'],
        ]);

        $room->update($data + ['is_active' => $request->boolean('is_active')]);
        ActivityLog::record('master_data_changed', $room, 'Unit kamar '.$room->code.' diperbarui.');

        return redirect()->route('owner.rooms.index')->with('success', 'Unit kamar diperbarui.');
    }
}
