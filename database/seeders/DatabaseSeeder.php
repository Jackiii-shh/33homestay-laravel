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
            DemoDataSeeder::class, // hapus baris ini bila tidak ingin data contoh
        ]);
    }
}
