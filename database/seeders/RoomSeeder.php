<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Database\Seeder;

/**
 * Data Resmi Unit Kamar 33HomeStay sesuai catatan buku kas riil Owner:
 * - K1 = Standard Room AC
 * - K2 = Standard Room AC
 * - K3 = Mezanin
 * - K4 = Standard Room Kipas
 * - K5 = Standard Room AC
 * - TH-1 = Tera House (Villa)
 */
class RoomSeeder extends Seeder
{
    public function run(): void
    {
        // 4 Tipe Kamar
        $typeAc = RoomType::updateOrCreate(['slug' => 'standard-room-ac'], [
            'name' => 'Standard Room AC',
            'capacity' => 2,
            'price_per_night' => 300000,
            'description' => 'Kamar standar ber-AC untuk 2 orang (Unit: K1, K2, K5). Nyaman, sejuk, dan dekat pantai.',
            'facilities' => ['AC', 'Kamar Mandi Dalam', 'Kapasitas 2 orang', 'WiFi'],
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $typeKipas = RoomType::updateOrCreate(['slug' => 'standard-room-kipas'], [
            'name' => 'Standard Room Kipas',
            'capacity' => 2,
            'price_per_night' => 200000,
            'description' => 'Kamar standar dengan kipas angin untuk 2 orang (Unit: K4). Ekonomis dan cocok untuk transit / staycation hemat.',
            'facilities' => ['Kipas Angin', 'Kamar Mandi Dalam', 'Kapasitas 2 orang', 'WiFi'],
            'is_active' => true,
            'sort_order' => 2,
        ]);

        $typeMezanin = RoomType::updateOrCreate(['slug' => 'mezanin'], [
            'name' => 'Mezanin',
            'capacity' => 4,
            'price_per_night' => 350000,
            'description' => 'Kamar mezanin dengan loteng bertingkat (Unit: K3), berkapasitas hingga 4 orang. Favorit keluarga dan grup kecil.',
            'facilities' => ['AC', 'Model Mezanin Bertingkat', 'Kapasitas hingga 4 orang', 'WiFi'],
            'is_active' => true,
            'sort_order' => 3,
        ]);

        $typeTera = RoomType::updateOrCreate(['slug' => 'tera-house'], [
            'name' => 'Tera House',
            'capacity' => 6,
            'price_per_night' => 800000,
            'description' => 'Unit villa terpisah eksklusif untuk keluarga besar / rombongan hingga 6 orang. Halaman privat dan fasilitas lengkap.',
            'facilities' => ['AC Lengkap', 'Ruang Keluarga Luas', 'Dapur & Kulkas', 'Kapasitas 4-6 orang', 'Parkir Privat'],
            'is_active' => true,
            'sort_order' => 4,
        ]);

        // 6 Unit Fisik sesuai data aktual Owner
        $units = [
            ['code' => 'K1', 'type_id' => $typeAc->id, 'notes' => 'Kamar 1 - Standard AC'],
            ['code' => 'K2', 'type_id' => $typeAc->id, 'notes' => 'Kamar 2 - Standard AC (Kamar paling sering dipesan)'],
            ['code' => 'K3', 'type_id' => $typeMezanin->id, 'notes' => 'Kamar 3 - Mezanin Bertingkat'],
            ['code' => 'K4', 'type_id' => $typeKipas->id, 'notes' => 'Kamar 4 - Standard Kipas (Favorit sewa transit)'],
            ['code' => 'K5', 'type_id' => $typeAc->id, 'notes' => 'Kamar 5 - Standard AC'],
            ['code' => 'TH-1', 'type_id' => $typeTera->id, 'notes' => 'Tera House - Villa Privat'],
        ];

        foreach ($units as $u) {
            Room::updateOrCreate(['code' => $u['code']], [
                'room_type_id' => $u['type_id'],
                'status' => Room::STATUS_READY,
                'notes' => $u['notes'],
                'is_active' => true,
            ]);
        }
    }
}
