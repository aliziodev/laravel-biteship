<?php

namespace Aliziodev\Biteship\DTOs\Order;

use Aliziodev\Biteship\Enums\OrderStatus;

class OrderResponse
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $reference_id,
        public readonly OrderStatus $status,
        public readonly string $courier_company,
        public readonly string $courier_type,
        public readonly ?string $courier_tracking_id,
        public readonly ?string $waybill_id,
        public readonly int $price,
        public readonly int $insurance_fee,
        public readonly int $cod_fee,
        public readonly array $origin,
        public readonly array $destination,
        public readonly array $items,
        public readonly array $raw,           // raw response untuk label generation
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'] ?? '',
            reference_id: $data['reference_id'] ?? null,
            status: OrderStatus::from($data['status'] ?? 'confirmed'),
            courier_company: $data['courier']['company'] ?? '',
            courier_type: $data['courier']['type'] ?? '',
            courier_tracking_id: $data['courier']['tracking_id'] ?? null,
            waybill_id: $data['courier']['waybill_id'] ?? null,
            price: $data['price'] ?? 0,
            insurance_fee: $data['insurance']['fee'] ?? 0,
            cod_fee: $data['destination']['cash_on_delivery_fee'] ?? 0,
            origin: $data['origin'] ?? [],
            destination: $data['destination'] ?? [],
            items: $data['items'] ?? [],
            raw: $data,
        );
    }

    public function isCancellable(): bool
    {
        return $this->status->canCancel();
    }

    public function isFinal(): bool
    {
        return $this->status->isFinal();
    }
}
