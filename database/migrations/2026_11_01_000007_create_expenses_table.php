<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            // tetap (fixed) | variabel (variable)
            $table->string('type', 20)->default('variabel');
            // gaji | listrik | makanan | oprasional | barang | bonus | wifi | netflix | sampah | sewa_kamar | kasbon | lainnya
            $table->string('category', 30);
            $table->string('name', 150); // Contoh: Token Listrik K1, Gaji Sadam, Beras 5kg, wifi, dll
            $table->unsignedBigInteger('amount');
            $table->date('expense_date');
            $table->string('room_code', 20)->nullable(); // K1, K2, K3, K4, K5, TH-1 (opsional bila spesifik unit)
            $table->string('paid_to', 80)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['expense_date', 'type']);
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
