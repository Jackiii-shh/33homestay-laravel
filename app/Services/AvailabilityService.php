<?php

namespace App\Services;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Sumber kebenaran tunggal ketersediaan kamar.
 *
 * Aturan inti anti double booking:
 *  - Rentang menginap bersifat [check_in, check_out) -> malam terakhir = check_out - 1.
 *  - Dua reservasi bentrok bila: A.check_in < B.check_out  DAN  A.check_out > B.check_in.
 *  - Tamu A check-out tanggal X dan tamu B check-in tanggal X TIDAK dianggap bentrok.
 *  - Hanya reservasi berstatus aktif (pending_dp, confirmed, checked_in) yang memblokir kamar.
 */
class AvailabilityService
{
    /** Jumlah malam antara dua tanggal (Y-m-d). */
    public static function nights(string|Carbon $in, string|Carbon $out): int
    {
        $a = Carbon::parse($in)->startOfDay()->getTimestamp();
        $b = Carbon::parse($out)->startOfDay()->getTimestamp();

        return (int) round(($b - $a) / 86400);
    }

    /** Query reservasi aktif yang beririsan dengan rentang [in, out). */
    public function overlapQuery(string $in, string $out, ?int $ignoreReservationId = null): Builder
    {
        return Reservation::query()
            ->whereIn('status', Reservation::ACTIVE_STATUSES)
            ->where('check_in', '<', $out)
            ->where('check_out', '>', $in)
            ->when($ignoreReservationId, fn (Builder $q) => $q->where('id', '!=', $ignoreReservationId));
    }

    public function conflictFor(int $roomId, string $in, string $out, ?int $ignoreReservationId = null): ?Reservation
    {
        return $this->overlapQuery($in, $out, $ignoreReservationId)
            ->where('room_id', $roomId)
            ->orderBy('check_in')
            ->first();
    }

    /** Unit kamar yang kosong pada rentang tanggal tertentu. */
    public function availableRooms(string $in, string $out, ?int $roomTypeId = null, ?int $ignoreReservationId = null): Collection
    {
        $busyRoomIds = $this->overlapQuery($in, $out, $ignoreReservationId)->pluck('room_id')->unique()->all();

        return Room::query()
            ->with('type')
            ->bookable()
            ->when($roomTypeId, fn (Builder $q) => $q->where('room_type_id', $roomTypeId))
            ->whereNotIn('id', $busyRoomIds)
            ->orderBy('code')
            ->get();
    }

    /**
     * Ringkasan per tipe kamar untuk halaman publik & chatbot.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function summaryByType(string $in, string $out, ?int $guests = null, ?int $roomTypeId = null): Collection
    {
        $nights = max(1, self::nights($in, $out));
        $busyRoomIds = $this->overlapQuery($in, $out)->pluck('room_id')->unique()->all();

        return RoomType::query()
            ->where('is_active', true)
            ->when($roomTypeId, fn (Builder $q) => $q->where('id', $roomTypeId))
            ->orderBy('sort_order')
            ->get()
            ->map(function (RoomType $type) use ($busyRoomIds, $nights, $guests) {
                $bookable = Room::query()->bookable()->where('room_type_id', $type->id)->pluck('id');
                $available = $bookable->diff($busyRoomIds)->count();

                return [
                    'type' => $type,
                    'total_units' => $bookable->count(),
                    'available_units' => $available,
                    'nights' => $nights,
                    'price_per_night' => $type->price_per_night,
                    'total_price' => $type->price_per_night * $nights,
                    'fits_guests' => $guests === null || $guests <= $type->capacity,
                ];
            });
    }

    /**
     * Saran tanggal terdekat (durasi sama) yang masih ada kamar kosong.
     *
     * @return array<int, array{check_in: string, check_out: string, units: int}>
     */
    public function nearbyWindows(?int $roomTypeId, string $in, string $out, int $limit = 3): array
    {
        $nights = max(1, self::nights($in, $out));
        $results = [];

        foreach ([1, -1, 2, -2, 3, -3, 4, 5, 6, 7] as $shift) {
            $start = Carbon::parse($in)->addDays($shift);
            if ($start->lt(today())) {
                continue;
            }
            $end = $start->copy()->addDays($nights);
            $units = $this->availableRooms($start->toDateString(), $end->toDateString(), $roomTypeId)->count();

            if ($units > 0) {
                $results[] = [
                    'check_in' => $start->toDateString(),
                    'check_out' => $end->toDateString(),
                    'units' => $units,
                ];
            }
            if (count($results) >= $limit) {
                break;
            }
        }

        return $results;
    }

    /**
     * Status setiap unit pada tanggal tertentu: tersedia | dibooking | terisi | dibersihkan | maintenance.
     *
     * @return Collection<int, array<string, mixed>>
     */
    public function roomStatusOn(Carbon $date): Collection
    {
        $d = $date->toDateString();

        $reservations = Reservation::query()
            ->whereIn('status', Reservation::ACTIVE_STATUSES)
            ->where('check_in', '<=', $d)
            ->where('check_out', '>', $d)
            ->get()
            ->keyBy('room_id');

        return Room::query()->with('type')->where('is_active', true)->orderBy('code')->get()
            ->map(function (Room $room) use ($reservations) {
                $res = $reservations->get($room->id);

                if ($room->status === Room::STATUS_MAINTENANCE) {
                    [$state, $label, $color] = ['maintenance', 'Maintenance', 'bg-slate-200 text-slate-700'];
                } elseif ($res && $res->status === Reservation::STATUS_CHECKED_IN) {
                    [$state, $label, $color] = ['terisi', 'Terisi', 'bg-emerald-100 text-emerald-800'];
                } elseif ($res) {
                    [$state, $label, $color] = ['dibooking', 'Dibooking', 'bg-sky-100 text-sky-800'];
                } elseif ($room->status === Room::STATUS_CLEANING) {
                    [$state, $label, $color] = ['dibersihkan', 'Dibersihkan', 'bg-amber-100 text-amber-800'];
                } else {
                    [$state, $label, $color] = ['tersedia', 'Tersedia', 'bg-white text-emerald-700 ring-1 ring-emerald-200'];
                }

                return compact('room', 'state', 'label', 'color') + ['reservation' => $res];
            });
    }

    /** Malam terjual pada rentang [start, end] (inklusif) untuk menghitung okupansi. */
    public function soldRoomNights(Carbon $start, Carbon $end, ?int $roomTypeId = null): int
    {
        $from = $start->copy()->startOfDay();
        $toExclusive = $end->copy()->startOfDay()->addDay();

        $reservations = Reservation::query()
            ->whereIn('status', [
                Reservation::STATUS_CONFIRMED,
                Reservation::STATUS_CHECKED_IN,
                Reservation::STATUS_CHECKED_OUT,
            ])
            ->where('check_in', '<', $toExclusive->toDateString())
            ->where('check_out', '>', $from->toDateString())
            ->when($roomTypeId, fn (Builder $q) => $q->whereHas('room', fn (Builder $r) => $r->where('room_type_id', $roomTypeId)))
            ->get(['check_in', 'check_out']);

        $nights = 0;
        foreach ($reservations as $r) {
            $s = $r->check_in->gt($from) ? $r->check_in : $from;
            $e = $r->check_out->lt($toExclusive) ? $r->check_out : $toExclusive;
            $nights += max(0, self::nights($s, $e));
        }

        return $nights;
    }
}
