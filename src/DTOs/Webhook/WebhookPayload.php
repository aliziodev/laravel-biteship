<?php

namespace Aliziodev\Biteship\DTOs\Webhook;

abstract class WebhookPayload
{
    public function __construct(
        public readonly string $event,
        public readonly string $order_id,
        public readonly array $raw,
    ) {}
}
