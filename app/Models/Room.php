<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Room extends Model
{
    public const STATUS_READY = 'ready';
    public const STATUS_CLEANING = 'cleaning';
    public const STATUS_MAINTENANCE = 'maintenance';

    public const STATUSES = [
        self::STATUS_READY => 'Siap',
        self::STATUS_CLEANING => 'Sedang dibersihkan',
        self::STATUS_MAINTENANCE => 'Maintenance',
    ];

    protected $fillable = ['room_type_id', 'code', 'status', 'notes', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(RoomType::class, 'room_type_id');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    /** Unit yang boleh dipesan: aktif dan tidak sedang maintenance. */
    public function scopeBookable(Builder $query): Builder
    {
        return $query->where('rooms.is_active', true)
            ->where('rooms.status', '!=', self::STATUS_MAINTENANCE);
    }
}
