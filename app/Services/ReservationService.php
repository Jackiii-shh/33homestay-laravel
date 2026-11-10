<?php

namespace App\Services;

use App\Exceptions\RoomConflictException;
use App\Models\ActivityLog;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Seluruh perubahan reservasi melewati service ini sehingga validasi bentrok
 * SELALU dijalankan di dalam transaksi + penguncian baris kamar (race-condition safe).
 */
class ReservationService
{
    public function __construct(
        private readonly AvailabilityService $availability,
        private readonly PaymentService $payments,
    ) {}

    // ------------------------------------------------------------------ buat
    public function create(array $data, ?User $user = null): Reservation
    {
        try {
            return DB::transaction(function () use ($data, $user) {
                $room = $this->lockRoom((int) $data['room_id']);
                $in = Carbon::parse($data['check_in'])->toDateString();
                $out = Carbon::parse($data['check_out'])->toDateString();

                $nights = $this->validateStay($room, $in, $out, (int) $data['guest_count']);
                $this->assertNoConflict($room, $in, $out);   // <-- inti pencegahan double booking

                $channel = $data['channel'] ?? 'whatsapp';
                $isOta = in_array($channel, Reservation::OTA_CHANNELS, true);
                $price = (int) $room->type->price_per_night;

                $reservation = Reservation::create([
                    'code' => Reservation::generateCode(),
                    'room_id' => $room->id,
                    'guest_name' => $data['guest_name'],
                    'guest_city' => $data['guest_city'] ?? null,
                    'guest_phone' => normalize_phone($data['guest_phone'] ?? null),
                    'guest_count' => (int) $data['guest_count'],
                    'check_in' => $in,
                    'check_out' => $out,
                    'nights' => $nights,
                    'price_per_night' => $price,
                    'total_price' => $price * $nights,
                    'status' => $isOta ? Reservation::STATUS_CONFIRMED : Reservation::STATUS_PENDING,
                    'channel' => $channel,
                    'dp_deadline' => $isOta ? null : now()->addHours((int) config('homestay.dp_deadline_hours', 6)),
                    'notes' => $data['notes'] ?? null,
                    'created_by' => $user?->id,
                    'updated_by' => $user?->id,
                ]);

                ActivityLog::record(
                    'reservation_created',
                    $reservation,
                    sprintf(
                        'Reservasi %s dibuat: %s, kamar %s, %s s.d. %s (%d malam), total %s, kanal %s.',
                        $reservation->code, $reservation->guest_name, $room->code,
                        $reservation->check_in->translatedFormat('d M Y'), $reservation->check_out->translatedFormat('d M Y'),
                        $nights, rupiah($reservation->total_price), $reservation->channelLabel(),
                    ),
                    [], $user?->id,
                );

                if ($isOta) {
                    $this->payments->record($reservation, [
                        'amount' => $reservation->total_price,
                        'method' => 'ota',
                        'note' => 'Pembayaran melalui '.$reservation->channelLabel(),
                    ], $user);
                }

                return $reservation->fresh(['room.type', 'payments']);
            });
        } catch (RoomConflictException $e) {
            $this->logConflict($e, $user, 'membuat reservasi');
            throw $e;
        }
    }

    // ----------------------------------------------------------------- ubah
    /** Ubah data / tanggal / kamar, atau perpanjang inap. Selalu memeriksa ulang bentrok. */
    public function update(Reservation $reservation, array $data, ?User $user = null): Reservation
    {
        try {
            return DB::transaction(function () use ($reservation, $data, $user) {
                $r = Reservation::with('room.type')->whereKey($reservation->id)->lockForUpdate()->firstOrFail();

                if (! $r->isActiveBooking()) {
                    throw ValidationException::withMessages([
                        'check_out' => 'Reservasi berstatus "'.$r->statusLabel().'" tidak dapat diubah.',
                    ]);
                }

                $stayLocked = $r->status === Reservation::STATUS_CHECKED_IN; // tamu sudah masuk: hanya boleh ubah check-out
                $roomId = $stayLocked ? (int) $r->room_id : (int) ($data['room_id'] ?? $r->room_id);
                $in = $stayLocked ? $r->check_in->toDateString() : Carbon::parse($data['check_in'])->toDateString();
                $out = Carbon::parse($data['check_out'])->toDateString();

                if ($stayLocked && $out < today()->toDateString()) {
                    throw ValidationException::withMessages([
                        'check_out' => 'Check-out baru tidak boleh sebelum hari ini karena tamu sudah check-in.',
                    ]);
                }

                $room = $this->lockRoom($roomId, checkUsable: $roomId !== (int) $r->room_id);
                $guests = (int) ($data['guest_count'] ?? $r->guest_count);
                $nights = $this->validateStay($room, $in, $out, $guests);
                $this->assertNoConflict($room, $in, $out, $r->id);

                $price = (int) $room->room_type_id === (int) $r->room->room_type_id
                    ? (int) $r->price_per_night
                    : (int) $room->type->price_per_night;
                $total = $price * $nights;
                $paid = (int) $r->payments()->sum('amount');

                if ($paid > $total) {
                    throw ValidationException::withMessages([
                        'check_out' => 'Total baru ('.rupiah($total).') lebih kecil dari pembayaran yang sudah masuk ('.rupiah($paid).'). Batalkan & refund secara manual.',
                    ]);
                }

                $old = [
                    'kamar' => $r->room->code,
                    'check_in' => $r->check_in->toDateString(),
                    'check_out' => $r->check_out->toDateString(),
                    'total' => (int) $r->total_price,
                ];
                $datesChanged = (int) $r->room_id !== $roomId
                    || $old['check_in'] !== $in
                    || $old['check_out'] !== $out;

                $r->fill([
                    'room_id' => $roomId,
                    'guest_name' => $data['guest_name'] ?? $r->guest_name,
                    'guest_city' => $data['guest_city'] ?? $r->guest_city,
                    'guest_phone' => array_key_exists('guest_phone', $data) ? normalize_phone($data['guest_phone']) : $r->guest_phone,
                    'guest_count' => $guests,
                    'check_in' => $in,
                    'check_out' => $out,
                    'nights' => $nights,
                    'price_per_night' => $price,
                    'total_price' => $total,
                    'notes' => array_key_exists('notes', $data) ? $data['notes'] : $r->notes,
                    'updated_by' => $user?->id,
                ]);

                $otaNote = '';
                if ($datesChanged && $r->ota_blocked_at !== null) {
                    $r->ota_blocked_at = null;
                    $r->ota_unblocked_at = null;
                    $otaNote = ' Tanggal lama perlu DIBUKA dan tanggal baru perlu DIBLOKIR ulang di OTA.';
                }
                $r->save();
                $r->load('room');

                ActivityLog::record(
                    'reservation_updated',
                    $r,
                    sprintf(
                        'Reservasi %s diubah: kamar %s -> %s, %s s.d. %s -> %s s.d. %s, total %s -> %s.%s',
                        $r->code, $old['kamar'], $r->room->code,
                        $old['check_in'], $old['check_out'], $in, $out,
                        rupiah($old['total']), rupiah($total), $otaNote,
                    ),
                    ['old' => $old, 'new' => ['kamar' => $r->room->code, 'check_in' => $in, 'check_out' => $out, 'total' => $total]],
                    $user?->id,
                );

                return $r->fresh(['room.type', 'payments']);
            });
        } catch (RoomConflictException $e) {
            $this->logConflict($e, $user, 'mengubah/memperpanjang reservasi');
            throw $e;
        }
    }

    // ---------------------------------------------------------------- batal
    public function cancel(Reservation $reservation, string $reason, int $refund = 0, ?User $user = null): Reservation
    {
        return DB::transaction(function () use ($reservation, $reason, $refund, $user) {
            $r = $this->lockReservation($reservation);

            if (! in_array($r->status, [Reservation::STATUS_PENDING, Reservation::STATUS_CONFIRMED], true)) {
                throw ValidationException::withMessages([
                    'reason' => 'Hanya reservasi "Menunggu DP" atau "Terkonfirmasi" yang dapat dibatalkan. Tamu yang sudah check-in harus melalui check-out.',
                ]);
            }

            $paid = (int) $r->payments()->sum('amount');
            if ($refund > $paid) {
                throw ValidationException::withMessages([
                    'refund_amount' => 'Refund ('.rupiah($refund).') tidak boleh melebihi total pembayaran masuk ('.rupiah($paid).').',
                ]);
            }

            $r->update([
                'status' => Reservation::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'cancel_reason' => $reason,
                'refund_amount' => $refund,
                'dp_forfeited' => $paid > $refund,
                'dp_deadline' => null,
                'updated_by' => $user?->id,
            ]);

            ActivityLog::record(
                'reservation_cancelled',
                $r,
                sprintf('Reservasi %s dibatalkan. Alasan: %s. Dibayar %s, refund %s.', $r->code, $reason, rupiah($paid), rupiah($refund)),
                ['paid' => $paid, 'refund' => $refund],
                $user?->id,
            );

            return $r;
        });
    }

    /** Dipanggil scheduler: batalkan reservasi "Menunggu DP" yang lewat batas waktu & belum ada pembayaran. */
    public function autoExpire(Reservation $reservation): bool
    {
        return DB::transaction(function () use ($reservation) {
            $r = $this->lockReservation($reservation);

            if ($r->status !== Reservation::STATUS_PENDING || (int) $r->payments()->sum('amount') > 0) {
                return false;
            }

            $r->update([
                'status' => Reservation::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'cancel_reason' => 'Batas waktu DP terlewat (dibatalkan otomatis oleh sistem)',
                'dp_deadline' => null,
            ]);

            ActivityLog::record(
                'reservation_auto_expired',
                $r,
                sprintf('Reservasi %s a.n. %s dibatalkan otomatis: DP tidak masuk sebelum batas waktu. Tanggal kembali tersedia.', $r->code, $r->guest_name),
            );

            return true;
        });
    }

    // -------------------------------------------------------------- no-show
    public function markNoShow(Reservation $reservation, ?User $user = null): Reservation
    {
        return DB::transaction(function () use ($reservation, $user) {
            $r = $this->lockReservation($reservation);

            if ($r->status !== Reservation::STATUS_CONFIRMED) {
                throw ValidationException::withMessages(['status' => 'Hanya reservasi "Terkonfirmasi" yang dapat ditandai no-show.']);
            }
            if (now()->lt($r->checkInCutoff())) {
                throw ValidationException::withMessages([
                    'status' => 'Belum melewati batas check-in ('.$r->checkInCutoff()->translatedFormat('d M Y H:i').'). Tunggu hingga batas waktu terlewat.',
                ]);
            }

            $paid = (int) $r->payments()->sum('amount');
            $forfeit = (bool) config('homestay.dp_forfeit_on_no_show', true);

            $r->update([
                'status' => Reservation::STATUS_NO_SHOW,
                'cancelled_at' => now(),
                'dp_forfeited' => $forfeit && $paid > 0,
                'refund_amount' => $forfeit ? 0 : $paid,
                'updated_by' => $user?->id,
            ]);

            ActivityLog::record(
                'reservation_no_show',
                $r,
                sprintf('Reservasi %s a.n. %s ditandai NO-SHOW. %s Kamar kembali tersedia.', $r->code, $r->guest_name, $forfeit ? 'DP hangus ('.rupiah($paid).').' : 'DP dikembalikan.'),
                ['paid' => $paid, 'forfeited' => $forfeit],
                $user?->id,
            );

            return $r;
        });
    }

    // ------------------------------------------------------------- check-in
    public function checkIn(Reservation $reservation, ?User $user = null): Reservation
    {
        return DB::transaction(function () use ($reservation, $user) {
            $r = $this->lockReservation($reservation);
            $r->load('room');

            if ($r->status !== Reservation::STATUS_CONFIRMED) {
                throw ValidationException::withMessages(['status' => 'Hanya reservasi "Terkonfirmasi" yang dapat check-in.']);
            }
            if ($r->check_in->gt(today())) {
                throw ValidationException::withMessages(['status' => 'Check-in baru dapat dilakukan mulai '.$r->check_in->translatedFormat('d M Y').'.']);
            }
            if ($r->check_out->lte(today())) {
                throw ValidationException::withMessages(['status' => 'Masa menginap reservasi ini sudah lewat.']);
            }
            if ($r->room->status !== Room::STATUS_READY) {
                throw ValidationException::withMessages([
                    'status' => 'Kamar '.$r->room->code.' berstatus "'.(Room::STATUSES[$r->room->status] ?? $r->room->status).'". Tandai kamar siap terlebih dahulu.',
                ]);
            }

            $balance = (int) $r->total_price - (int) $r->payments()->sum('amount');
            if ($balance > 0) {
                throw ValidationException::withMessages([
                    'balance' => 'Pelunasan belum selesai. Sisa tagihan '.rupiah($balance).' - catat pelunasan terlebih dahulu.',
                ]);
            }

            $r->update(['status' => Reservation::STATUS_CHECKED_IN, 'checked_in_at' => now(), 'updated_by' => $user?->id]);

            ActivityLog::record('reservation_checked_in', $r, sprintf('Check-in %s a.n. %s di kamar %s.', $r->code, $r->guest_name, $r->room->code), [], $user?->id);

            return $r;
        });
    }

    // ------------------------------------------------------------ check-out
    public function checkOut(Reservation $reservation, ?User $user = null): Reservation
    {
        return DB::transaction(function () use ($reservation, $user) {
            $r = $this->lockReservation($reservation);
            $r->load('room');

            if ($r->status !== Reservation::STATUS_CHECKED_IN) {
                throw ValidationException::withMessages(['status' => 'Hanya tamu berstatus "Check-in" yang dapat check-out.']);
            }

            $balance = (int) $r->total_price - (int) $r->payments()->sum('amount');
            if ($balance > 0) {
                throw ValidationException::withMessages([
                    'balance' => 'Masih ada sisa tagihan '.rupiah($balance).'. Selesaikan pembayaran sebelum check-out.',
                ]);
            }

            $r->update(['status' => Reservation::STATUS_CHECKED_OUT, 'checked_out_at' => now(), 'updated_by' => $user?->id]);
            $r->room->update(['status' => Room::STATUS_CLEANING]);

            ActivityLog::record('reservation_checked_out', $r, sprintf('Check-out %s a.n. %s dari kamar %s. Kamar masuk pembersihan.', $r->code, $r->guest_name, $r->room->code), [], $user?->id);

            return $r;
        });
    }

    // ------------------------------------------------------------ checklist OTA
    public function markOtaBlocked(Reservation $reservation, ?User $user = null): void
    {
        $reservation->update(['ota_blocked_at' => now(), 'ota_unblocked_at' => null]);
        ActivityLog::record('ota_blocked', $reservation, sprintf('Tanggal reservasi %s (%s s.d. %s) ditandai sudah DIBLOKIR di OTA.', $reservation->code, $reservation->check_in->toDateString(), $reservation->check_out->toDateString()), [], $user?->id);
    }

    public function markOtaUnblocked(Reservation $reservation, ?User $user = null): void
    {
        $reservation->update(['ota_unblocked_at' => now()]);
        ActivityLog::record('ota_unblocked', $reservation, sprintf('Tanggal reservasi %s ditandai sudah DIBUKA kembali di OTA.', $reservation->code), [], $user?->id);
    }

    // ------------------------------------------------------------- internal
    private function lockReservation(Reservation $reservation): Reservation
    {
        return Reservation::query()->whereKey($reservation->id)->lockForUpdate()->firstOrFail();
    }

    private function lockRoom(int $roomId, bool $checkUsable = true): Room
    {
        $room = Room::with('type')->whereKey($roomId)->lockForUpdate()->firstOrFail();

        if ($checkUsable && (! $room->is_active || $room->status === Room::STATUS_MAINTENANCE)) {
            throw ValidationException::withMessages([
                'room_id' => 'Kamar '.$room->code.' tidak dapat dipesan (nonaktif atau sedang maintenance).',
            ]);
        }

        return $room;
    }

    private function validateStay(Room $room, string $in, string $out, int $guests): int
    {
        $nights = AvailabilityService::nights($in, $out);

        if ($nights < 1) {
            throw ValidationException::withMessages(['check_out' => 'Tanggal check-out harus setelah check-in.']);
        }
        if ($nights > (int) config('homestay.max_stay_nights', 14)) {
            throw ValidationException::withMessages(['check_out' => 'Maksimal menginap '.config('homestay.max_stay_nights', 14).' malam per reservasi.']);
        }
        if ($guests > $room->type->capacity) {
            throw ValidationException::withMessages([
                'guest_count' => 'Kapasitas '.$room->type->name.' maksimal '.$room->type->capacity.' orang.',
            ]);
        }

        return $nights;
    }

    private function assertNoConflict(Room $room, string $in, string $out, ?int $ignoreReservationId = null): void
    {
        $conflict = $this->availability->conflictFor($room->id, $in, $out, $ignoreReservationId);

        if ($conflict) {
            throw new RoomConflictException($room, $conflict, $in, $out);
        }
    }

    private function logConflict(RoomConflictException $e, ?User $user, string $context): void
    {
        ActivityLog::record(
            'booking_conflict_blocked',
            $e->conflict,
            'Percobaan '.$context.' DITOLAK sistem: '.$e->getMessage(),
            ['room' => $e->room->code, 'check_in' => $e->checkIn, 'check_out' => $e->checkOut],
            $user?->id,
        );
    }
}
