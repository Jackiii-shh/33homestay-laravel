<?php

namespace App\Http\Controllers\Staff;

use App\Exceptions\RoomConflictException;
use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Payment;
use App\Models\Reservation;
use App\Models\Room;
use App\Services\AvailabilityService;
use App\Services\ReservationService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
    public function __construct(
        private readonly ReservationService $reservations,
        private readonly AvailabilityService $availability,
    ) {}

    // ------------------------------------------------- daftar & detail (owner + pengelola)
    public function index(Request $request)
    {
        $query = Reservation::with('room.type')->withSum('payments', 'amount');

        if ($q = trim((string) $request->query('q'))) {
            $query->where(fn ($w) => $w
                ->where('code', 'like', "%{$q}%")
                ->orWhere('guest_name', 'like', "%{$q}%")
                ->orWhere('guest_phone', 'like', "%{$q}%"));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }
        if ($request->filled('channel')) {
            $query->where('channel', $request->query('channel'));
        }
        if ($request->filled('from')) {
            $query->where('check_out', '>', $request->query('from'));
        }
        if ($request->filled('to')) {
            $query->where('check_in', '<=', $request->query('to'));
        }

        $reservations = $query->orderByDesc('check_in')->orderByDesc('id')->paginate(15)->withQueryString();

        return view('staff.reservations.index', compact('reservations'));
    }

    public function show(Reservation $reservation)
    {
        $reservation->load(['room.type', 'creator', 'payments.receiver', 'payments.invoice']);

        $logs = ActivityLog::with('user')
            ->where('subject_type', Reservation::class)
            ->where('subject_id', $reservation->id)
            ->latest()->limit(30)->get();

        return view('staff.reservations.show', compact('reservation', 'logs'));
    }

    // ---------------------------------------------------------------- buat
    public function create(Request $request)
    {
        $rooms = Room::with('type')->bookable()->orderBy('code')->get();

        return view('staff.reservations.create', [
            'rooms' => $rooms,
            'prefill' => $request->only(['room_id', 'check_in', 'check_out', 'guest_count']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'guest_name' => ['required', 'string', 'max:100'],
            'guest_city' => ['nullable', 'string', 'max:80'],
            'guest_phone' => ['nullable', 'string', 'max:25'],
            'guest_count' => ['required', 'integer', 'min:1', 'max:10'],
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'check_in' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'check_out' => ['required', 'date_format:Y-m-d', 'after:check_in'],
            'channel' => ['required', Rule::in(array_keys(Reservation::CHANNELS))],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $reservation = $this->reservations->create($data, $request->user());
        } catch (RoomConflictException $e) {
            return back()->withInput()
                ->withErrors(['room_id' => $e->getMessage()])
                ->with('alternatives', $this->alternatives($e));
        }

        $msg = $reservation->status === Reservation::STATUS_PENDING
            ? 'Reservasi tersimpan, tanggal kamar sudah terblokir. Selanjutnya blokir tanggal di OTA, kirim rincian biaya, dan catat DP sebelum '.$reservation->dp_deadline->translatedFormat('d M H:i').'.'
            : 'Reservasi OTA tersimpan dan langsung terkonfirmasi.';

        return redirect()->route('reservations.show', $reservation)->with('success', $msg);
    }

    // ---------------------------------------------------------------- ubah
    public function edit(Reservation $reservation)
    {
        if (! $reservation->isActiveBooking()) {
            return redirect()->route('reservations.show', $reservation)
                ->with('error', 'Reservasi berstatus "'.$reservation->statusLabel().'" tidak dapat diubah.');
        }

        $reservation->load('room.type');
        $rooms = Room::with('type')->where('is_active', true)->orderBy('code')->get();

        return view('staff.reservations.edit', compact('reservation', 'rooms'));
    }

    public function update(Request $request, Reservation $reservation)
    {
        $locked = $reservation->status === Reservation::STATUS_CHECKED_IN;

        $rules = [
            'guest_name' => ['required', 'string', 'max:100'],
            'guest_city' => ['nullable', 'string', 'max:80'],
            'guest_phone' => ['nullable', 'string', 'max:25'],
            'guest_count' => ['required', 'integer', 'min:1', 'max:10'],
            'check_out' => ['required', 'date_format:Y-m-d'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
        if (! $locked) {
            $rules += [
                'room_id' => ['required', 'integer', 'exists:rooms,id'],
                'check_in' => ['required', 'date_format:Y-m-d'],
            ];
        }
        $data = $request->validate($rules);

        try {
            $oldOta = $reservation->ota_blocked_at;
            $updated = $this->reservations->update($reservation, $data, $request->user());
        } catch (RoomConflictException $e) {
            return back()->withInput()
                ->withErrors(['check_out' => $e->getMessage()])
                ->with('alternatives', $this->alternatives($e));
        }

        $msg = 'Reservasi diperbarui; validasi bentrok lolos dan kalender sudah diperbarui.';
        if ($oldOta && $updated->ota_blocked_at === null) {
            $msg .= ' PENTING: buka tanggal lama dan blokir tanggal baru di OTA.';
        }

        return redirect()->route('reservations.show', $updated)->with('success', $msg);
    }

    // ------------------------------------------------------------- aksi status
    public function checkIn(Request $request, Reservation $reservation)
    {
        $this->reservations->checkIn($reservation, $request->user());

        return back()->with('success', 'Check-in berhasil dicatat. Status kamar: Terisi.');
    }

    public function checkOut(Request $request, Reservation $reservation)
    {
        $this->reservations->checkOut($reservation, $request->user());

        return back()->with('success', 'Check-out berhasil. Kamar masuk status "Sedang dibersihkan" - tandai siap setelah selesai.');
    }

    public function noShow(Request $request, Reservation $reservation)
    {
        $this->reservations->markNoShow($reservation, $request->user());

        return back()->with('success', 'Reservasi ditandai no-show. Tanggal kamar kembali tersedia.');
    }

    public function cancel(Request $request, Reservation $reservation)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'max:200'],
            'refund_amount' => ['nullable', 'integer', 'min:0'],
        ]);

        $this->reservations->cancel($reservation, $data['reason'], (int) ($data['refund_amount'] ?? 0), $request->user());

        return back()->with('success', 'Reservasi dibatalkan. Tanggal kamar kembali tersedia.');
    }

    public function otaBlock(Request $request, Reservation $reservation)
    {
        $this->reservations->markOtaBlocked($reservation, $request->user());

        return back()->with('success', 'Ditandai: tanggal sudah diblokir di OTA.');
    }

    public function otaUnblock(Request $request, Reservation $reservation)
    {
        $this->reservations->markOtaUnblocked($reservation, $request->user());

        return back()->with('success', 'Ditandai: tanggal sudah dibuka kembali di OTA.');
    }

    // ----------------------------------------------------- saran saat terjadi bentrok
    private function alternatives(RoomConflictException $e): array
    {
        $in = $e->checkIn;
        $out = $e->checkOut;
        $typeId = (int) $e->room->room_type_id;

        $free = $this->availability->availableRooms($in, $out);

        return [
            'conflict' => ['code' => $e->conflict->code, 'id' => $e->conflict->id],
            'same_type' => $free->where('room_type_id', $typeId)->map(fn (Room $r) => ['id' => $r->id, 'label' => $r->code.' ('.$r->type->name.')'])->values()->all(),
            'other_type' => $free->where('room_type_id', '!=', $typeId)->map(fn (Room $r) => ['id' => $r->id, 'label' => $r->code.' ('.$r->type->name.')'])->values()->all(),
            'windows' => $this->availability->nearbyWindows($typeId, $in, $out),
            'check_in' => $in,
            'check_out' => $out,
        ];
    }
}
