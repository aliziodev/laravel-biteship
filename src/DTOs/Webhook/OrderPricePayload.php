<?php

namespace Aliziodev\Biteship\DTOs\Webhook;

class OrderPricePayload extends WebhookPayload
{
    public function __construct(
        string $orderId,
        array $raw,
        public readonly int $price,
        public readonly int $insuranceFee,
    ) {
        parent::__construct('order.price', $orderId, $raw);
    }

    public static function fromArray(array $data): static
    {
        return new static(
            orderId: $data['order_id'] ?? $data['id'] ?? '',
            raw: $data,
            price: $data['price'] ?? 0,
            insuranceFee: $data['insurance']['fee'] ?? 0,
        );
    }
}
