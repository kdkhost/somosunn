<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Order extends Model
{
    use HasFactory;

    /**
     * Rotulos legiveis por tipo de venda (item_type do OrderItem).
     * A ordem reflete a prioridade ao determinar o tipo principal do pedido.
     */
    public const SALE_TYPE_LABELS = [
        'event' => 'Evento',
        'event_exhibitor_area' => 'Expositor',
        'mentorship' => 'Mentoria',
        'course' => 'Curso',
        'seller_product' => 'Marketplace',
        'plan' => 'Plano/Assinatura',
    ];

    protected $fillable = [
        'user_id',
        'seller_id',
        'status',
        'paid_at',
        'cancelled_at',
        'total_amount',
        'fee_amount',
        'platform_fee_amount',
        'currency',
        'gateway',
        'payment_method',
        'is_manual_approval',
        'manual_approved_by',
        'manual_approved_at',
        'gateway_account_id',
        'metadata',
        'transaction_id',
        'refunded_at'
    ];

    protected $casts = [
        'metadata' => 'array',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'manual_approved_at' => 'datetime',
        'is_manual_approval' => 'boolean',
        'total_amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'platform_fee_amount' => 'decimal:2',
        'refunded_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class); // Buyer
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function shipment()
    {
        return $this->hasOne(OrderShipment::class);
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class);
    }

    public function exhibitorRegistrations()
    {
        return $this->hasMany(EventExhibitorRegistration::class);
    }
    
    public function gatewayAccount()
    {
        return $this->belongsTo(GatewayAccount::class);
    }

    public function manualApprover()
    {
        return $this->belongsTo(User::class, 'manual_approved_by');
    }

    public function scopeFinancialPaid($query)
    {
        return $query
            ->where('status', 'paid')
            ->where(function ($q) {
                $q->whereNull('is_manual_approval')->orWhere('is_manual_approval', false);
            });
    }

    public function scopeManualApproved($query)
    {
        return $query
            ->where('status', 'paid')
            ->where('is_manual_approval', true);
    }

    /**
     * Filtra pedidos que contenham ao menos um item do tipo informado.
     */
    public function scopeOfSaleType($query, ?string $type)
    {
        $type = trim((string) $type);
        if ($type === '' || !array_key_exists($type, self::SALE_TYPE_LABELS)) {
            return $query;
        }

        return $query->whereHas('items', function ($itemQuery) use ($type) {
            $itemQuery->where('item_type', $type);
        });
    }

    /**
     * Retorna o tipo principal de venda do pedido com base nos itens,
     * com fallback para o sale_type/context gravado no metadata.
     */
    public function primarySaleType(): ?string
    {
        $items = $this->relationLoaded('items') ? $this->items : $this->items()->get();

        foreach (array_keys(self::SALE_TYPE_LABELS) as $type) {
            if ($items->contains(fn ($item) => (string) $item->item_type === $type)) {
                return $type;
            }
        }

        $metaType = trim((string) data_get($this->metadata, 'sale_type', ''));
        if ($metaType !== '' && array_key_exists($metaType, self::SALE_TYPE_LABELS)) {
            return $metaType;
        }

        return null;
    }

    /**
     * Rotulo legivel do tipo principal de venda.
     */
    public function saleTypeLabel(): string
    {
        $type = $this->primarySaleType();

        return $type !== null ? self::SALE_TYPE_LABELS[$type] : 'Outro';
    }

    /**
     * Endereco do comprador formatado em linha unica (a partir do usuario).
     */
    public function buyerAddress(): string
    {
        $user = $this->relationLoaded('user') ? $this->user : $this->user()->first();
        if (!$user) {
            return '';
        }

        $street = trim((string) ($user->street ?? ''));
        $number = trim((string) ($user->number ?? ''));
        $line = $street;
        if ($number !== '') {
            $line = $line !== '' ? $line . ', ' . $number : $number;
        }

        $parts = array_filter([
            $line,
            trim((string) ($user->complement ?? '')),
            trim((string) ($user->neighborhood ?? '')),
            trim((string) ($user->city ?? '')),
            trim((string) ($user->state ?? '')),
            trim((string) ($user->cep ?? '')),
        ], fn ($value) => $value !== '');

        if (empty($parts)) {
            return trim((string) ($user->address ?? ''));
        }

        return implode(' - ', $parts);
    }

    public function getChargedAmountAttribute(): float
    {
        $chargedAmount = data_get($this->metadata, 'refunds.charged_amount');
        if (is_numeric($chargedAmount) && (float) $chargedAmount > 0) {
            return round((float) $chargedAmount, 2);
        }

        $webhookAmount = data_get($this->metadata, 'webhook_data.transaction_amount');
        if (is_numeric($webhookAmount) && (float) $webhookAmount > 0) {
            return round((float) $webhookAmount, 2);
        }

        return round((float) $this->total_amount, 2);
    }

    public function getRefundedAmountAttribute(): float
    {
        if ((string) $this->status === 'refunded') {
            return $this->charged_amount;
        }

        $refundedAmount = data_get($this->metadata, 'refunds.total_amount');
        if (is_numeric($refundedAmount) && (float) $refundedAmount > 0) {
            return min($this->charged_amount, round((float) $refundedAmount, 2));
        }

        return 0.0;
    }

    public function getRemainingRefundableAmountAttribute(): float
    {
        return max(0.0, round($this->charged_amount - $this->refunded_amount, 2));
    }

    public function getIsPartiallyRefundedAttribute(): bool
    {
        return $this->refunded_amount > 0 && !$this->is_fully_refunded;
    }

    public function getIsFullyRefundedAttribute(): bool
    {
        return (string) $this->status === 'refunded' || $this->remaining_refundable_amount <= 0.009;
    }

    public function getLastRefundAtAttribute(): ?Carbon
    {
        $lastRefundAt = data_get($this->metadata, 'refunds.last_refunded_at');
        if (is_string($lastRefundAt) && $lastRefundAt !== '') {
            return Carbon::parse($lastRefundAt);
        }

        return $this->refunded_at;
    }

    public function supportsPartialRefund(): bool
    {
        return (string) $this->gateway === 'mercadopago'
            && (string) $this->status === 'paid'
            && !$this->is_manual_approval
            && !empty($this->transaction_id)
            && $this->remaining_refundable_amount > 0.009;
    }
}
