<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Services\PaymentService;
use App\Services\ReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    private function reservationStartingToday(): Reservation
    {
        $type = RoomType::create([
            'name' => 'Standard Room AC', 'slug' => 'standard-room-ac', 'capacity' => 2,
            'price_per_night' => 300000, 'is_active' => true, 'sort_order' => 1,
        ]);
        $room = Room::create(['room_type_id' => $type->id, 'code' => 'AC-1', 'status' => 'ready', 'is_active' => true]);

        return app(ReservationService::class)->create([
            'room_id' => $room->id, 'guest_name' => 'Tamu Uji', 'guest_count' => 2,
            'check_in' => today()->toDateString(), 'check_out' => today()->addDays(2)->toDateString(),
            'channel' => 'whatsapp',
        ]);
    }

    public function test_dp_below_minimum_is_rejected(): void
    {
        $r = $this->reservationStartingToday(); // total 600.000, DP min 50% = 300.000
        $this->expectException(ValidationException::class);
        app(PaymentService::class)->record($r, ['amount' => 100000, 'method' => 'transfer']);
    }

    public function test_full_lifecycle_requires_full_payment_before_checkin(): void
    {
        $r = $this->reservationStartingToday();
        $payments = app(PaymentService::class);
        $service = app(ReservationService::class);

        $dp = $payments->record($r, ['amount' => 300000, 'method' => 'transfer']);
        $r->refresh();
        $this->assertSame(Reservation::STATUS_CONFIRMED, $r->status);
        $this->assertSame('dp', $dp->type);
        $this->assertStringStartsWith('INV-', $dp->invoice->number);

        try {
            $service->checkIn($r);
            $this->fail('Check-in seharusnya ditolak sebelum lunas.');
        } catch (ValidationException) {
            $this->assertSame(Reservation::STATUS_CONFIRMED, $r->fresh()->status);
        }

        $final = $payments->record($r->fresh(), ['amount' => 300000, 'method' => 'cash']);
        $this->assertSame('pelunasan', $final->type);
        $this->assertSame(2, $r->invoices()->count());

        $service->checkIn($r->fresh());
        $this->assertSame(Reservation::STATUS_CHECKED_IN, $r->fresh()->status);

        $service->checkOut($r->fresh());
        $this->assertSame(Reservation::STATUS_CHECKED_OUT, $r->fresh()->status);
        $this->assertSame(Room::STATUS_CLEANING, $r->room->fresh()->status);
    }

    public function test_overpayment_is_rejected(): void
    {
        $r = $this->reservationStartingToday();
        $this->expectException(ValidationException::class);
        app(PaymentService::class)->record($r, ['amount' => 900000, 'method' => 'transfer']);
    }

    public function test_ota_booking_is_confirmed_and_paid_via_ota(): void
    {
        $r = $this->reservationStartingToday();
        $room = $r->room;

        $ota = app(ReservationService::class)->create([
            'room_id' => $room->id, 'guest_name' => 'Tamu OTA', 'guest_count' => 2,
            'check_in' => today()->addDays(5)->toDateString(), 'check_out' => today()->addDays(6)->toDateString(),
            'channel' => 'agoda',
        ]);

        $this->assertSame(Reservation::STATUS_CONFIRMED, $ota->status);
        $this->assertSame('lunas', $ota->payment_status);
        $this->assertSame('ota', $ota->payments->first()->method);
    }
}
