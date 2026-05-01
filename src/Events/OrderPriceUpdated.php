<?php

namespace Aliziodev\Biteship\Events;

use Aliziodev\Biteship\DTOs\Webhook\OrderPricePayload;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPriceUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly OrderPricePayload $payload,
    ) {}
}
