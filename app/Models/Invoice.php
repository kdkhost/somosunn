<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'user_id',
        'order_id',
        'created_by',
        'status',
        'currency',
        'subtotal',
        'discount_amount',
        'total_amount',
        'issued_at',
        'due_at',
        'paid_at',
        'email_queued_at',
        'email_sent_at',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'issued_at' => 'datetime',
        'due_at' => 'datetime',
        'paid_at' => 'datetime',
        'email_queued_at' => 'datetime',
        'email_sent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function ensureNumber(): void
    {
        if (!empty($this->number)) {
            return;
        }

        $year = $this->issued_at?->format('Y') ?? $this->created_at?->format('Y') ?? now()->format('Y');
        $this->number = 'FAT-' . $year . '-' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }
}

