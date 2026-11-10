<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoomTypeController extends Controller
{
    public function index()
    {
        return view('owner.room-types.index', [
            'types' => RoomType::withCount('rooms')->orderBy('sort_order')->get(),
        ]);
    }

    public function create()
    {
        return view('owner.room-types.edit', [
            'type' => new RoomType(['capacity' => 2, 'price_per_night' => 0, 'is_active' => true]),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $base = Str::slug($data['name']) ?: 'tipe-kamar';
        $slug = $base;
        $i = 2;
        while (RoomType::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        $type = RoomType::create([
            'slug' => $slug,
            'sort_order' => (int) RoomType::max('sort_order') + 1,
        ] + $this->payload($data, $request));

        ActivityLog::record('master_data_changed', $type, sprintf('Tipe kamar baru "%s" ditambahkan (kapasitas %d, %s/malam). Tambahkan unitnya di menu Unit Kamar.', $type->name, $type->capacity, rupiah($type->price_per_night)));

        return redirect()->route('owner.rooms.create')->with('success', 'Tipe kamar "'.$type->name.'" ditambahkan. Sekarang tambahkan unit kamarnya.');
    }

    public function edit(RoomType $roomType)
    {
        return view('owner.room-types.edit', ['type' => $roomType]);
    }

    public function update(Request $request, RoomType $roomType)
    {
        $data = $this->validated($request);

        $old = ['harga' => $roomType->price_per_night, 'kapasitas' => $roomType->capacity];

        $roomType->update($this->payload($data, $request));

        ActivityLog::record(
            'master_data_changed', $roomType,
            sprintf('Tipe kamar "%s" diubah. Harga %s -> %s, kapasitas %d -> %d. (Reservasi lama tetap memakai harga saat dibuat.)',
                $roomType->name, rupiah($old['harga']), rupiah($roomType->price_per_night), $old['kapasitas'], $roomType->capacity),
        );

        return redirect()->route('owner.room-types.index')->with('success', 'Tipe kamar diperbarui.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'capacity' => ['required', 'integer', 'min:1', 'max:20'],
            'price_per_night' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string', 'max:500'],
            'facilities' => ['nullable', 'string', 'max:500'],
            'is_active' => ['nullable', 'boolean'],
        ]);
    }

    private function payload(array $data, Request $request): array
    {
        return [
            'name' => $data['name'],
            'capacity' => $data['capacity'],
            'price_per_night' => $data['price_per_night'],
            'description' => $data['description'] ?? null,
            'facilities' => collect(preg_split('/[\r\n,]+/', (string) ($data['facilities'] ?? '')))
                ->map(fn ($f) => trim($f))->filter()->values()->all(),
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
