<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\URL;

class Invoice extends Model
{
    public const TYPE_TITLES = [
        'dp' => 'INVOICE DP',
        'pelunasan' => 'STRUK PELUNASAN',
        'full' => 'INVOICE LUNAS',
    ];

    protected $fillable = [
        'number', 'token', 'reservation_id', 'payment_id', 'type', 'amount', 'issued_at', 'issued_by',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'datetime',
            'amount' => 'integer',
        ];
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function title(): string
    {
        return self::TYPE_TITLES[$this->type] ?? 'INVOICE';
    }

    /** Halaman publik untuk memeriksa keaslian invoice (tamu / owner). */
    public function verifyUrl(): string
    {
        return route('invoice.verify', ['number' => $this->number, 'token' => $this->token]);
    }

    /** Tautan unduh PDF bertanda tangan (berlaku 30 hari) untuk dikirim ke tamu. */
    public function publicPdfUrl(): string
    {
        return URL::temporarySignedRoute('invoice.public.pdf', now()->addDays(30), ['token' => $this->token]);
    }
}
