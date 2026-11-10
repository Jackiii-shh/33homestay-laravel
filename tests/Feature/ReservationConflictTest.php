<?php

namespace Tests\Feature;

use App\Exceptions\RoomConflictException;
use App\Models\ActivityLog;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\AvailabilityService;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** Inti sistem: reservasi yang bertabrakan HARUS ditolak (anti double booking). */
class ReservationConflictTest extends TestCase
{
    use RefreshDatabase;

    private function rooms(): array
    {
        $type = RoomType::create([
            'name' => 'Standard Room AC', 'slug' => 'standard-room-ac', 'capacity' => 2,
            'price_per_night' => 300000, 'is_active' => true, 'sort_order' => 1,
        ]);
        $a = Room::create(['room_type_id' => $type->id, 'code' => 'AC-1', 'status' => 'ready', 'is_active' => true]);
        $b = Room::create(['room_type_id' => $type->id, 'code' => 'AC-2', 'status' => 'ready', 'is_active' => true]);

        return [$a, $b];
    }

    private function payload(Room $room, int $inDays, int $outDays, array $extra = []): array
    {
        return array_merge([
            'room_id' => $room->id,
            'guest_name' => 'Tamu Uji',
            'guest_count' => 2,
            'check_in' => today()->addDays($inDays)->toDateString(),
            'check_out' => today()->addDays($outDays)->toDateString(),
            'channel' => 'whatsapp',
        ], $extra);
    }

    public function test_overlapping_dates_on_same_room_are_rejected_and_logged(): void
    {
        [$a] = $this->rooms();
        $service = app(ReservationService::class);

        $service->create($this->payload($a, 10, 12));

        $this->expectException(RoomConflictException::class);
        try {
            $service->create($this->payload($a, 11, 13));
        } finally {
            $this->assertSame(1, Reservation::count());
            $this->assertSame(1, ActivityLog::where('action', 'booking_conflict_blocked')->count());
        }
    }

    public function test_same_day_checkout_and_checkin_is_not_a_conflict(): void
    {
        [$a] = $this->rooms();
        $service = app(ReservationService::class);

        $service->create($this->payload($a, 10, 12));
        $second = $service->create($this->payload($a, 12, 14));

        $this->assertSame(2, Reservation::count());
        $this->assertSame(Reservation::STATUS_PENDING, $second->status);
    }

    public function test_same_dates_on_different_room_are_allowed(): void
    {
        [$a, $b] = $this->rooms();
        $service = app(ReservationService::class);

        $service->create($this->payload($a, 10, 12));
        $service->create($this->payload($b, 10, 12));

        $this->assertSame(2, Reservation::count());
    }

    public function test_cancelled_reservation_frees_the_dates(): void
    {
        [$a] = $this->rooms();
        $service = app(ReservationService::class);

        $first = $service->create($this->payload($a, 10, 12));
        $service->cancel($first, 'Tamu batal', 0);

        $service->create($this->payload($a, 10, 12));
        $this->assertSame(1, Reservation::where('status', Reservation::STATUS_PENDING)->count());
    }

    public function test_available_rooms_excludes_busy_rooms_and_maintenance(): void
    {
        [$a, $b] = $this->rooms();
        $service = app(ReservationService::class);
        $availability = app(AvailabilityService::class);

        $service->create($this->payload($a, 10, 12));
        $in = today()->addDays(10)->toDateString();
        $out = today()->addDays(12)->toDateString();

        $this->assertSame(['AC-2'], $availability->availableRooms($in, $out)->pluck('code')->all());

        $b->update(['status' => Room::STATUS_MAINTENANCE]);
        $this->assertCount(0, $availability->availableRooms($in, $out));
    }

    public function test_extending_a_stay_into_another_booking_is_rejected(): void
    {
        [$a] = $this->rooms();
        $service = app(ReservationService::class);

        $first = $service->create($this->payload($a, 10, 12));
        $service->create($this->payload($a, 13, 15));

        $this->expectException(RoomConflictException::class);
        $service->update($first, [
            'room_id' => $a->id, 'check_in' => $first->check_in->toDateString(),
            'check_out' => today()->addDays(14)->toDateString(), 'guest_count' => 2,
        ]);
    }

    public function test_nights_helper(): void
    {
        $this->assertSame(2, AvailabilityService::nights('2026-12-24', '2026-12-26'));
        $this->assertSame(0, AvailabilityService::nights('2026-12-24', '2026-12-24'));
    }
}
