<?php

namespace App\Exceptions;

use App\Models\Reservation;
use App\Models\Room;
use RuntimeException;

/** Dilempar saat rentang tanggal bertabrakan dengan reservasi aktif lain (DOUBLE BOOKING dicegah). */
class RoomConflictException extends RuntimeException
{
    public function __construct(
        public readonly Room $room,
        public readonly Reservation $conflict,
        public readonly string $checkIn,
        public readonly string $checkOut,
    ) {
        parent::__construct(sprintf(
            'Kamar %s sudah dipesan pada %s s.d. %s (reservasi %s a.n. %s, status %s). Reservasi TIDAK disimpan.',
            $room->code,
            $conflict->check_in->translatedFormat('d M Y'),
            $conflict->check_out->translatedFormat('d M Y'),
            $conflict->code,
            $conflict->guest_name,
            $conflict->statusLabel(),
        ));
    }
}
