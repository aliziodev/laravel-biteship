<?php

namespace Aliziodev\Biteship\DTOs\Webhook;

use Aliziodev\Biteship\Enums\OrderStatus;

class OrderStatusPayload extends WebhookPayload
{
    public function __construct(
        string $orderId,
        array $raw,
        public readonly OrderStatus $status,
        public readonly ?string $waybillId,
        public readonly ?string $courierTrackingId,
    ) {
        parent::__construct('order.status', $orderId, $raw);
    }

    public static function fromArray(array $data): static
    {
        return new static(
            orderId: $data['order_id'] ?? $data['id'] ?? '',
            raw: $data,
            status: OrderStatus::from($data['status'] ?? 'confirmed'),
            waybillId: $data['courier']['waybill_id'] ?? null,
            courierTrackingId: $data['courier']['tracking_id'] ?? null,
        );
    }
}
