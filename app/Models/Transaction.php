<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    // Status pesanan (alur dapur)
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'diproses';
    public const STATUS_DONE = 'selesai';

    // Status pembayaran
    public const PAY_UNPAID = 'unpaid';
    public const PAY_PENDING = 'pending';
    public const PAY_PAID = 'paid';
    public const PAY_FAILED = 'failed';
    public const PAY_EXPIRED = 'expired';

    protected $fillable = [
        'invoice_number',
        'user_id',
        'customer_name',
        'note',
        'total',
        'paid',
        'change',
        'payment_method',
        'payment_status',
        'payment_ref',
        'payment_details',
        'paid_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'paid' => 'decimal:2',
            'change' => 'decimal:2',
            'paid_at' => 'datetime',
            'payment_details' => 'array',
        ];
    }

    public function isPaid(): bool
    {
        return $this->payment_status === self::PAY_PAID;
    }

    public function paymentStatusLabel(): string
    {
        return match ($this->payment_status) {
            self::PAY_UNPAID => 'Belum Bayar',
            self::PAY_PENDING => 'Menunggu Pembayaran',
            self::PAY_PAID => 'Lunas',
            self::PAY_FAILED => 'Gagal',
            self::PAY_EXPIRED => 'Kedaluwarsa',
            default => ucfirst((string) $this->payment_status),
        };
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    /**
     * Generate nomor invoice unik, format: INV-YYYYMMDD-XXXX
     */
    public static function generateInvoiceNumber(): string
    {
        $date = now()->format('Ymd');
        $prefix = "INV-{$date}-";

        $lastToday = static::where('invoice_number', 'like', $prefix.'%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $sequence = $lastToday ? ((int) substr($lastToday, -4)) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PROCESSING => 'Diproses',
            self::STATUS_DONE => 'Selesai',
            default => ucfirst($this->status),
        };
    }
}
