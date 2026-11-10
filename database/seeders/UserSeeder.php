<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // GANTI kata sandi setelah login pertama (menu Profil).
        User::updateOrCreate(['email' => 'owner@33homestay.test'], [
            'name' => 'Taufiq (Owner)',
            'phone' => '081200000001',
            'role' => User::ROLE_OWNER,
            'is_active' => true,
            'password' => 'password',
        ]);

        User::updateOrCreate(['email' => 'pengelola@33homestay.test'], [
            'name' => 'Pengelola 33HomeStay',
            'phone' => '081200000002',
            'role' => User::ROLE_PENGELOLA,
            'is_active' => true,
            'password' => 'password',
        ]);
    }
}
