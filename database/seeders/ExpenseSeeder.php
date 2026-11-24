<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ExpenseSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::where('role', User::ROLE_OWNER)->first();

        // 1. Biaya Tetap Bulanan (Fixed Costs)
        $fixedExpenses = [
            ['name' => 'Gaji Wawan / Sadam (Pengelola Lapangan)', 'category' => 'gaji', 'amount' => 1500000, 'type' => 'tetap'],
            ['name' => 'Gaji Angga / Alewo (Staf Pembantu)', 'category' => 'gaji', 'amount' => 500000, 'type' => 'tetap'],
            ['name' => 'Langganan Netflix Smart TV Homestay', 'category' => 'netflix', 'amount' => 186000, 'type' => 'tetap'],
            ['name' => 'Internet WiFi Indihome', 'category' => 'wifi', 'amount' => 170000, 'type' => 'tetap'],
            ['name' => 'Iuran Sampah Lingkungan', 'category' => 'sampah', 'amount' => 100000, 'type' => 'tetap'],
            ['name' => 'Sewa Operasional Kamar 5', 'category' => 'sewa_kamar', 'amount' => 550000, 'type' => 'tetap'],
        ];

        // Sebar biaya tetap untuk 3 bulan terakhir (Sep, Okt, Nov 2026)
        $months = ['2026-09-01', '2026-10-01', '2026-11-01'];
        foreach ($months as $mStr) {
            $mDate = Carbon::parse($mStr);
            foreach ($fixedExpenses as $item) {
                Expense::create([
                    'type' => $item['type'],
                    'category' => $item['category'],
                    'name' => $item['name'],
                    'amount' => $item['amount'],
                    'expense_date' => $mDate->copy()->addDays(mt_rand(1, 5)),
                    'created_by' => $owner?->id,
                ]);
            }
        }

        // 2. Biaya Variabel (Token Listrik K1-K5, Makanan, Perawatan)
        $rooms = ['K1', 'K2', 'K3', 'K4', 'K5'];
        foreach ($months as $mStr) {
            $mDate = Carbon::parse($mStr);
            // Token listrik tiap kamar
            foreach ($rooms as $rCode) {
                Expense::create([
                    'type' => 'variabel',
                    'category' => 'listrik',
                    'name' => "Token Listrik Kamar {$rCode}",
                    'room_code' => $rCode,
                    'amount' => 100000,
                    'expense_date' => $mDate->copy()->addDays(mt_rand(5, 25)),
                    'created_by' => $owner?->id,
                ]);
            }

            // Operasional harian: galon, gas, kopi, pembersihan
            $ops = [
                ['name' => 'Air Galon & Gas LPG', 'category' => 'makanan', 'amount' => 50000],
                ['name' => 'Kopi, Gula, Teh Tamu', 'category' => 'makanan', 'amount' => 45000],
                ['name' => 'Perlengkapan Kebersihan (Rinso & Pewangi)', 'category' => 'barang', 'amount' => 125000],
                ['name' => 'Bonus Operasional Staf Lembur', 'category' => 'bonus', 'amount' => 200000],
            ];
            foreach ($ops as $op) {
                Expense::create([
                    'type' => 'variabel',
                    'category' => $op['category'],
                    'name' => $op['name'],
                    'amount' => $op['amount'],
                    'expense_date' => $mDate->copy()->addDays(mt_rand(5, 28)),
                    'created_by' => $owner?->id,
                ]);
            }
        }
    }
}
