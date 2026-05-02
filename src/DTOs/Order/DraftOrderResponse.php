<?php

namespace Aliziodev\Biteship\DTOs\Order;

use Aliziodev\Biteship\Enums\DraftOrderStatus;

class DraftOrderResponse
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $order_id,
        public readonly ?string $reference_id,
        public readonly DraftOrderStatus $status,
        public readonly ?string $courier_company,
        public readonly ?string $courier_type,
        public readonly int $price,
        public readonly int $insurance_fee,
        public readonly int $cod_fee,
        public readonly array $origin,
        public readonly array $destination,
        public readonly array $delivery,
        public readonly array $items,
        public readonly ?string $invoice_id,
        public readonly ?string $placed_at,
        public readonly ?string $ready_at,
        public readonly ?string $confirmed_at,
        public readonly array $raw,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'] ?? '',
            order_id: $data['order_id'] ?? null,
            reference_id: $data['reference_id'] ?? null,
            status: DraftOrderStatus::from($data['status'] ?? 'placed'),
            courier_company: $data['courier']['company'] ?? null,
            courier_type: $data['courier']['type'] ?? null,
            price: $data['price'] ?? 0,
            insurance_fee: $data['courier']['insurance']['fee'] ?? 0,
            cod_fee: $data['destination']['cash_on_delivery']['fee'] ?? 0,
            origin: $data['origin'] ?? [],
            destination: $data['destination'] ?? [],
            delivery: $data['delivery'] ?? [],
            items: $data['items'] ?? [],
            invoice_id: $data['invoice_id'] ?? null,
            placed_at: $data['placed_at'] ?? null,
            ready_at: $data['ready_at'] ?? null,
            confirmed_at: $data['confirmed_at'] ?? null,
            raw: $data,
        );
    }
}
