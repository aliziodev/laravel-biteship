<?php

namespace Aliziodev\Biteship\DTOs\Order;

use Aliziodev\Biteship\Enums\OrderStatus;

class OrderResponse
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $referenceId,
        public readonly OrderStatus $status,
        public readonly string $courierCompany,
        public readonly string $courierType,
        public readonly ?string $courierTrackingId,
        public readonly ?string $waybillId,
        public readonly int $price,
        public readonly int $insuranceFee,
        public readonly int $codFee,
        public readonly array $origin,
        public readonly array $destination,
        public readonly array $items,
        public readonly array $raw,           // raw response untuk label generation
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'] ?? '',
            referenceId: $data['reference_id'] ?? null,
            status: OrderStatus::from($data['status'] ?? 'confirmed'),
            courierCompany: $data['courier']['company'] ?? '',
            courierType: $data['courier']['type'] ?? '',
            courierTrackingId: $data['courier']['tracking_id'] ?? null,
            waybillId: $data['courier']['waybill_id'] ?? null,
            price: $data['price'] ?? 0,
            insuranceFee: $data['insurance']['fee'] ?? 0,
            codFee: $data['destination']['cash_on_delivery_fee'] ?? 0,
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
