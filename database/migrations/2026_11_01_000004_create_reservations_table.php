<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique();
            $table->foreignId('room_id')->constrained('rooms')->restrictOnDelete();

            $table->string('guest_name', 100);
            $table->string('guest_city', 80)->nullable();
            $table->string('guest_phone', 30)->nullable();
            $table->unsignedTinyInteger('guest_count')->default(1);

            $table->date('check_in');
            $table->date('check_out'); // eksklusif: tamu keluar pada tanggal ini
            $table->unsignedSmallInteger('nights');
            $table->unsignedInteger('price_per_night');
            $table->unsignedBigInteger('total_price');

            // pending_dp | confirmed | checked_in | checked_out | cancelled | no_show
            $table->string('status', 20)->default('pending_dp');
            // whatsapp | agoda | tiketcom | walk_in
            $table->string('channel', 20)->default('whatsapp');

            $table->dateTime('dp_deadline')->nullable();
            $table->dateTime('ota_blocked_at')->nullable();
            $table->dateTime('ota_unblocked_at')->nullable();

            $table->text('notes')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->unsignedBigInteger('refund_amount')->default(0);
            $table->boolean('dp_forfeited')->default(false);

            $table->dateTime('checked_in_at')->nullable();
            $table->dateTime('checked_out_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['room_id', 'check_in', 'check_out']);
            $table->index('status');
            $table->index('check_in');
            $table->index('check_out');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
