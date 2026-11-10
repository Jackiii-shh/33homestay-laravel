<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

/**
 * DATA CONTOH: tipe & kapasitas sesuai hasil interview (Standard Room AC 2 orang,
 * Standard Room Kipas 2 orang, Mezanin hingga 4 orang). Jumlah unit mengikuti info Owner;
 * HARGA hanya contoh - Owner dapat mengubahnya dari dashboard (menu Tipe Kamar / Unit Kamar).
 */
class RoomSeeder extends Seeder
{
    public function run(): void
    {
        // Total 6 unit (info terbaru dari Owner): Standard AC 3, Standard Kipas 1, Mezanin 1, Tera House 1.
        $types = [
            ['slug' => 'standard-room-ac', 'name' => 'Standard Room AC', 'capacity' => 2, 'price' => 350000, 'prefix' => 'AC', 'units' => 3, 'sort' => 1,
                'description' => 'Kamar standar ber-AC untuk 2 orang.', 'facilities' => ['AC', 'Kapasitas 2 orang']],
            ['slug' => 'standard-room-kipas', 'name' => 'Standard Room Kipas', 'capacity' => 2, 'price' => 250000, 'prefix' => 'KIPAS', 'units' => 1, 'sort' => 2,
                'description' => 'Kamar standar dengan kipas angin untuk 2 orang.', 'facilities' => ['Kipas angin', 'Kapasitas 2 orang']],
            ['slug' => 'mezanin', 'name' => 'Mezanin', 'capacity' => 4, 'price' => 600000, 'prefix' => 'MZN', 'units' => 1, 'sort' => 3,
                'description' => 'Kamar mezanin untuk hingga 4 orang, cocok untuk keluarga atau grup kecil.', 'facilities' => ['Kapasitas hingga 4 orang']],
            // KAPASITAS & HARGA Tera House masih PLACEHOLDER - belum diinformasikan Owner. Ubah di dashboard Owner > Tipe & Harga Kamar.
            ['slug' => 'tera-house', 'name' => 'Tera House', 'capacity' => 4, 'price' => 800000, 'prefix' => 'TERA', 'units' => 1, 'sort' => 4,
                'description' => 'Hubungi pengelola untuk detail fasilitas dan kapasitas Tera House.', 'facilities' => []],
        ];

        foreach ($types as $t) {
            $type = RoomType::updateOrCreate(['slug' => $t['slug']], [
                'name' => $t['name'],
                'capacity' => $t['capacity'],
                'price_per_night' => $t['price'],
                'description' => $t['description'],
                'facilities' => $t['facilities'],
                'is_active' => true,
                'sort_order' => $t['sort'],
            ]);

            for ($i = 1; $i <= $t['units']; $i++) {
                Room::updateOrCreate(['code' => $t['prefix'].'-'.$i], [
                    'room_type_id' => $type->id,
                    'status' => Room::STATUS_READY,
                    'is_active' => true,
                ]);
            }
        }
    }
}
