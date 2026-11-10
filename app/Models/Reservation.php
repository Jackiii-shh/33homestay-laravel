<?php

namespace App\Models;

use App\Casts\DateOnly;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Reservation extends Model
{
    public const STATUS_PENDING = 'pending_dp';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CHECKED_IN = 'checked_in';
    public const STATUS_CHECKED_OUT = 'checked_out';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_NO_SHOW = 'no_show';

    /** Status yang MEMBLOKIR tanggal kamar (dipakai validasi bentrok). */
    public const ACTIVE_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_CHECKED_IN,
    ];

    /** [label, kelas Tailwind badge] */
    public const STATUS_META = [
        self::STATUS_PENDING => ['Menunggu DP', 'bg-amber-100 text-amber-800 ring-amber-200'],
        self::STATUS_CONFIRMED => ['Terkonfirmasi', 'bg-sky-100 text-sky-800 ring-sky-200'],
        self::STATUS_CHECKED_IN => ['Check-in', 'bg-emerald-100 text-emerald-800 ring-emerald-200'],
        self::STATUS_CHECKED_OUT => ['Selesai', 'bg-slate-100 text-slate-700 ring-slate-200'],
        self::STATUS_CANCELLED => ['Dibatalkan', 'bg-rose-100 text-rose-800 ring-rose-200'],
        self::STATUS_NO_SHOW => ['No-show', 'bg-orange-100 text-orange-800 ring-orange-200'],
    ];

    public const CHANNELS = [
        'whatsapp' => 'WhatsApp Business',
        'agoda' => 'Agoda (OTA)',
        'tiketcom' => 'Tiket.com (OTA)',
        'walk_in' => 'Walk-in',
    ];

    /** Kanal OTA: sudah terbayar via OTA & tanggal tidak perlu diblokir manual di OTA. */
    public const OTA_CHANNELS = ['agoda', 'tiketcom'];

    protected $fillable = [
        'code', 'room_id', 'guest_name', 'guest_city', 'guest_phone', 'guest_count',
        'check_in', 'check_out', 'nights', 'price_per_night', 'total_price',
        'status', 'channel', 'dp_deadline', 'ota_blocked_at', 'ota_unblocked_at',
        'notes', 'cancel_reason', 'refund_amount', 'dp_forfeited',
        'checked_in_at', 'checked_out_at', 'cancelled_at', 'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'check_in' => DateOnly::class,
            'check_out' => DateOnly::class,
            'dp_deadline' => 'datetime',
            'ota_blocked_at' => 'datetime',
            'ota_unblocked_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'checked_out_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'dp_forfeited' => 'boolean',
            'guest_count' => 'integer',
            'nights' => 'integer',
            'price_per_night' => 'integer',
            'total_price' => 'integer',
            'refund_amount' => 'integer',
        ];
    }

    // ---------------------------------------------------------------- relasi
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // --------------------------------------------------------------- helper
    public static function generateCode(): string
    {
        do {
            $code = 'RSV-'.now()->format('ymd').'-'.strtoupper(Str::random(4));
        } while (static::where('code', $code)->exists());

        return $code;
    }

    public function statusLabel(): string
    {
        return self::STATUS_META[$this->status][0] ?? $this->status;
    }

    public function statusClasses(): string
    {
        return self::STATUS_META[$this->status][1] ?? 'bg-slate-100 text-slate-700 ring-slate-200';
    }

    public function channelLabel(): string
    {
        return self::CHANNELS[$this->channel] ?? $this->channel;
    }

    public function isActiveBooking(): bool
    {
        return in_array($this->status, self::ACTIVE_STATUSES, true);
    }

    public function isOtaChannel(): bool
    {
        return in_array($this->channel, self::OTA_CHANNELS, true);
    }

    /** Total yang sudah dibayar (pakai relasi yang sudah dimuat bila ada). */
    public function getPaidAmountAttribute(): int
    {
        if (array_key_exists('payments_sum_amount', $this->attributes)) {
            return (int) $this->attributes['payments_sum_amount'];
        }

        return (int) ($this->relationLoaded('payments')
            ? $this->payments->sum('amount')
            : $this->payments()->sum('amount'));
    }

    public function getBalanceAttribute(): int
    {
        return max(0, (int) $this->total_price - $this->paid_amount);
    }

    /** belum_bayar | dp | lunas */
    public function getPaymentStatusAttribute(): string
    {
        $paid = $this->paid_amount;

        if ($paid <= 0) {
            return 'belum_bayar';
        }

        return $paid >= $this->total_price ? 'lunas' : 'dp';
    }

    public function paymentStatusLabel(): string
    {
        return ['belum_bayar' => 'Belum bayar', 'dp' => 'DP', 'lunas' => 'Lunas'][$this->payment_status];
    }

    public function paymentStatusClasses(): string
    {
        return [
            'belum_bayar' => 'bg-rose-100 text-rose-800 ring-rose-200',
            'dp' => 'bg-amber-100 text-amber-800 ring-amber-200',
            'lunas' => 'bg-emerald-100 text-emerald-800 ring-emerald-200',
        ][$this->payment_status];
    }

    /** DP minimum (dibulatkan ke atas). */
    public function minimumDp(): int
    {
        $pct = max(1, min(100, (int) config('homestay.dp_percentage', 50)));

        return (int) ceil($this->total_price * $pct / 100);
    }

    /** Batas jam check-in pada hari H (untuk penentuan no-show). */
    public function checkInCutoff(): \Illuminate\Support\Carbon
    {
        return \Illuminate\Support\Carbon::parse(
            $this->check_in->toDateString().' '.config('homestay.checkin_cutoff', '22:00')
        );
    }

    /** Perlu diblokir manual di OTA? (kanal WhatsApp / walk-in, reservasi aktif, belum diblokir) */
    public function needsOtaBlock(): bool
    {
        return ! $this->isOtaChannel()
            && $this->isActiveBooking()
            && $this->ota_blocked_at === null
            && $this->check_out->gte(today());
    }

    /** Perlu dibuka lagi di OTA? (batal / no-show setelah sempat diblokir) */
    public function needsOtaUnblock(): bool
    {
        return in_array($this->status, [self::STATUS_CANCELLED, self::STATUS_NO_SHOW], true)
            && $this->ota_blocked_at !== null
            && $this->ota_unblocked_at === null
            && $this->check_out->gte(today());
    }
}
