<?php

namespace Aliziodev\Biteship\DTOs\Webhook;

use Aliziodev\Biteship\Enums\OrderStatus;

class OrderStatusPayload extends WebhookPayload
{
    public function __construct(
        string $order_id,
        array $raw,
        public readonly OrderStatus $status,
        public readonly ?string $waybill_id,
        public readonly ?string $courier_tracking_id,
    ) {
        parent::__construct('order.status', $order_id, $raw);
    }

    public static function fromArray(array $data): static
    {
        return new static(
            order_id: $data['order_id'] ?? $data['id'] ?? '',
            raw: $data,
            status: OrderStatus::from($data['status'] ?? 'confirmed'),
            waybill_id: $data['courier']['waybill_id'] ?? null,
            courier_tracking_id: $data['courier']['tracking_id'] ?? null,
        );
    }
}
