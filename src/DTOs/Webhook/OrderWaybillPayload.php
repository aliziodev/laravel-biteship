<?php

namespace Aliziodev\Biteship\DTOs\Webhook;

class OrderWaybillPayload extends WebhookPayload
{
    public function __construct(
        string $order_id,
        array $raw,
        public readonly string $waybill_id,
        public readonly ?string $courier_tracking_id,
    ) {
        parent::__construct('order.waybill_id', $order_id, $raw);
    }

    public static function fromArray(array $data): static
    {
        return new static(
            order_id: $data['order_id'] ?? $data['id'] ?? '',
            raw: $data,
            waybill_id: $data['courier']['waybill_id'] ?? '',
            courier_tracking_id: $data['courier']['tracking_id'] ?? null,
        );
    }
}
