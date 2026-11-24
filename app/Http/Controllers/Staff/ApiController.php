<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Services\AvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiController extends Controller
{
    /** Daftar unit kosong untuk rentang tanggal (dipakai formulir reservasi). */
    public function availableRooms(Request $request, AvailabilityService $availability): JsonResponse
    {
        $data = $request->validate([
            'check_in' => ['required', 'date_format:Y-m-d'],
            'check_out' => ['required', 'date_format:Y-m-d', 'after_or_equal:check_in'],
            'guests' => ['nullable', 'integer', 'min:1', 'max:10'],
            'ignore' => ['nullable', 'integer'],
        ]);

        $isTransit = $data['check_in'] === $data['check_out'];
        $nights = $isTransit ? 0 : AvailabilityService::nights($data['check_in'], $data['check_out']);

        $rooms = $availability
            ->availableRooms($data['check_in'], $data['check_out'], null, $data['ignore'] ?? null)
            ->filter(fn (Room $r) => empty($data['guests']) || $r->type->capacity >= (int) $data['guests'])
            ->map(function (Room $r) use ($nights, $isTransit) {
                if ($isTransit) {
                    $rate = match ($r->type->slug ?? '') {
                        'standard-room-kipas' => 170000,
                        'mezanin' => 220000,
                        default => 200000,
                    };
                    $label = $r->code.' - '.$r->type->name.' (TRANSIT Day-Use) - '.rupiah($rate);
                } else {
                    $rate = $r->type->price_per_night * $nights;
                    $label = $r->code.' - '.$r->type->name.' (maks. '.$r->type->capacity.' org) - '.rupiah($rate);
                }

                return [
                    'id' => $r->id,
                    'code' => $r->code,
                    'type' => $r->type->name,
                    'capacity' => $r->type->capacity,
                    'price_per_night' => $r->type->price_per_night,
                    'total' => $rate,
                    'is_transit' => $isTransit,
                    'label' => $label,
                ];
            })->values();

        return response()->json(['nights' => $nights, 'is_transit' => $isTransit, 'rooms' => $rooms]);
    }
}
