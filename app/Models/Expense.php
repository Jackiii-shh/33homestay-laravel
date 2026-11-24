<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    public const TYPE_TETAP = 'tetap';
    public const TYPE_VARIABEL = 'variabel';

    protected $fillable = [
        'type',
        'category',
        'name',
        'amount',
        'expense_date',
        'room_code',
        'paid_to',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function categoryLabel(): string
    {
        return match ($this->category) {
            'gaji' => 'Gaji Karyawan',
            'listrik' => 'Listrik / Token',
            'makanan' => 'Makanan & Konsumsi',
            'oprasional' => 'Operasional',
            'barang' => 'Barang & Perlengkapan',
            'bonus' => 'Bonus Staf',
            'wifi' => 'Internet WiFi',
            'netflix' => 'Netflix',
            'sampah' => 'Iuran Sampah',
            'sewa_kamar' => 'Sewa Kamar 5',
            'kasbon' => 'Kasbon Pengelola',
            default => ucfirst($this->category),
        };
    }
}
