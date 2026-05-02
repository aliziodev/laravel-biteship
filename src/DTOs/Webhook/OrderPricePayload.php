<?php

namespace Aliziodev\Biteship\DTOs\Webhook;

class OrderPricePayload extends WebhookPayload
{
    public function __construct(
        string $order_id,
        array $raw,
        public readonly int $price,
        public readonly int $insurance_fee,
    ) {
        parent::__construct('order.price', $order_id, $raw);
    }

    public static function fromArray(array $data): static
    {
        return new static(
            order_id: $data['order_id'] ?? $data['id'] ?? '',
            raw: $data,
            price: $data['price'] ?? $data['order_price'] ?? 0,
            insurance_fee: $data['insurance_fee'] ?? $data['insurance']['fee'] ?? 0,
        );
    }
}
