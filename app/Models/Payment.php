<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    public const METHODS = [
        'transfer' => 'Transfer bank',
        'cash' => 'Tunai (cash)',
        'qris' => 'QRIS',
        'ota' => 'Dibayar via OTA',
    ];

    public const TYPES = [
        'dp' => 'DP',
        'pelunasan' => 'Pelunasan',
        'full' => 'Lunas (sekali bayar)',
    ];

    protected $fillable = ['reservation_id', 'type', 'amount', 'method', 'paid_at', 'note', 'received_by'];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'amount' => 'integer',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function methodLabel(): string
    {
        return self::METHODS[$this->method] ?? $this->method;
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
