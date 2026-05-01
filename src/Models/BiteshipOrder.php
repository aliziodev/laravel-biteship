<?php

namespace Aliziodev\Biteship\Models;

use Aliziodev\Biteship\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BiteshipOrder extends Model
{
    protected $table = 'biteship_orders';

    protected $fillable = [
        'biteship_order_id',
        'biteship_status',
        'waybill_id',
        'courier_company',
        'courier_type',
        'courier_tracking_id',
        'shipping_cost',
        'insurance_cost',
        'cod_amount',
        'raw_response',
        'confirmed_at',
        'picked_at',
        'delivered_at',
    ];

    protected $casts = [
        'shipping_cost' => 'integer',
        'insurance_cost' => 'integer',
        'cod_amount' => 'integer',
        'raw_response' => 'array',
        'confirmed_at' => 'datetime',
        'picked_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    // --- Relations ---

    public function orderable(): MorphTo
    {
        return $this->morphTo();
    }

    // --- Accessors ---

    public function getStatusEnumAttribute(): ?OrderStatus
    {
        return $this->biteship_status
            ? OrderStatus::tryFrom($this->biteship_status)
            : null;
    }

    // --- Helpers ---

    public function isFinal(): bool
    {
        return $this->statusEnum?->isFinal() ?? false;
    }

    public function isDelivered(): bool
    {
        return $this->biteship_status === OrderStatus::Delivered->value;
    }

    public function isCod(): bool
    {
        return $this->cod_amount > 0;
    }

    public function canCancel(): bool
    {
        return $this->statusEnum?->canCancel() ?? false;
    }
}
