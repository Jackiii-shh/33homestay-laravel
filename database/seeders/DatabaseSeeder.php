<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            RoomSeeder::class,
            ExpenseSeeder::class,
            ActualReservationsSeeder::class, // Mengimpor seluruh 1.578 transaksi riil buku tamu owner
        ]);
    }
}
