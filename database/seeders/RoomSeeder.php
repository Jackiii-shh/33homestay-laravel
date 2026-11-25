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
 * - K4 = Standard Room Kipas (kamar mitra — skema bagi hasil)
 * - K5 = Standard Room AC
 *
 * Catatan: Tera House / TH-1 TIDAK termasuk dalam scope proyek ini
 *          (dikecualikan oleh owner Pak Taufiq).
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
            'description' => 'Unit villa terpisah eksklusif. Catatan: Tera House tidak termasuk scope proyek ini — dikecualikan oleh owner.',
            'facilities' => ['AC Lengkap', 'Ruang Keluarga Luas', 'Dapur & Kulkas', 'Kapasitas 4-6 orang', 'Parkir Privat'],
            'is_active' => false,  // nonaktif karena di luar scope
            'sort_order' => 4,
        ]);

        // 5 Unit Fisik sesuai scope proyek (TH-1 dikecualikan owner)
        $units = [
            [
                'code' => 'K1', 'type_id' => $typeAc->id,
                'notes' => 'Kamar 1 - Standard AC',
                'is_partner' => false, 'partner_share_percent' => 0,
            ],
            [
                'code' => 'K2', 'type_id' => $typeAc->id,
                'notes' => 'Kamar 2 - Standard AC',
                'is_partner' => false, 'partner_share_percent' => 0,
            ],
            [
                'code' => 'K3', 'type_id' => $typeMezanin->id,
                'notes' => 'Kamar 3 - Mezanin Bertingkat',
                'is_partner' => false, 'partner_share_percent' => 0,
            ],
            [
                'code' => 'K4', 'type_id' => $typeKipas->id,
                // K4 dikelola oleh mitra (Bahri). Pendapatan dibagi — proporsi
                // pasti belum dikonfirmasi ke owner, sementara diisi 50%.
                'notes' => 'Kamar 4 - Standard Kipas (kamar mitra bagi hasil)',
                'is_partner' => true, 'partner_share_percent' => 50,
            ],
            [
                'code' => 'K5', 'type_id' => $typeAc->id,
                'notes' => 'Kamar 5 - Standard AC',
                'is_partner' => false, 'partner_share_percent' => 0,
            ],
        ];

        foreach ($units as $u) {
            Room::updateOrCreate(['code' => $u['code']], [
                'room_type_id' => $u['type_id'],
                'status' => Room::STATUS_READY,
                'notes' => $u['notes'],
                'is_active' => true,
                'is_partner' => $u['is_partner'],
                'partner_share_percent' => $u['partner_share_percent'],
            ]);
        }
    }
}
