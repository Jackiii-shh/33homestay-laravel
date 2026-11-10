<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'action', 'subject_type', 'subject_id', 'description', 'properties', 'ip_address',
    ];

    protected function casts(): array
    {
        return ['properties' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Catat aktivitas (audit trail untuk mencegah kecurangan & menelusuri perubahan). */
    public static function record(string $action, ?Model $subject, string $description, array $properties = [], ?int $userId = null): self
    {
        return static::create([
            'user_id' => $userId ?? auth()->id(),
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
            'description' => $description,
            'properties' => $properties ?: null,
            'ip_address' => request()?->ip(),
        ]);
    }

    public const ACTION_LABELS = [
        'reservation_created' => 'Reservasi dibuat',
        'reservation_updated' => 'Reservasi diubah',
        'reservation_cancelled' => 'Reservasi dibatalkan',
        'reservation_auto_expired' => 'Batal otomatis (DP terlambat)',
        'reservation_checked_in' => 'Check-in',
        'reservation_checked_out' => 'Check-out',
        'reservation_no_show' => 'No-show',
        'payment_recorded' => 'Pembayaran dicatat',
        'booking_conflict_blocked' => 'Double booking DICEGAH',
        'ota_blocked' => 'Tanggal diblokir di OTA',
        'ota_unblocked' => 'Tanggal dibuka di OTA',
        'room_status_changed' => 'Status kamar diubah',
        'master_data_changed' => 'Data master diubah',
        'user_changed' => 'Pengguna diubah',
    ];

    public function actionLabel(): string
    {
        return self::ACTION_LABELS[$this->action] ?? $this->action;
    }
}
