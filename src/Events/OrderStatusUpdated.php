<?php

namespace Aliziodev\Biteship\Events;

use Aliziodev\Biteship\DTOs\Webhook\OrderStatusPayload;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly OrderStatusPayload $payload,
    ) {}
}
