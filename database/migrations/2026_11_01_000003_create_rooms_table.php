<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->constrained('room_types')->restrictOnDelete();
            $table->string('code', 30)->unique();           // K1, K2, K3, K4, K5
            $table->string('status', 20)->default('ready'); // ready | cleaning | maintenance
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);

            // Kamar mitra bagi hasil (contoh: K4 dikelola mitra, ada bagi hasil)
            $table->boolean('is_partner')->default(false)
                  ->comment('true jika kamar dikelola mitra dengan skema bagi hasil');
            $table->unsignedTinyInteger('partner_share_percent')->default(0)
                  ->comment('Persentase pendapatan yang menjadi hak mitra (0-100). 0 = bukan mitra.');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
