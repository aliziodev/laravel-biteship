<?php

namespace Aliziodev\Biteship\DTOs\Webhook;

class OrderWaybillPayload extends WebhookPayload
{
    public function __construct(
        string $orderId,
        array $raw,
        public readonly string $waybillId,
        public readonly ?string $courierTrackingId,
    ) {
        parent::__construct('order.waybill_id', $orderId, $raw);
    }

    public static function fromArray(array $data): static
    {
        return new static(
            orderId: $data['order_id'] ?? $data['id'] ?? '',
            raw: $data,
            waybillId: $data['courier']['waybill_id'] ?? '',
            courierTrackingId: $data['courier']['tracking_id'] ?? null,
        );
    }
}
