<?php

namespace Aliziodev\Biteship\Events;

use Aliziodev\Biteship\DTOs\Webhook\OrderWaybillPayload;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderWaybillUpdated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly OrderWaybillPayload $payload,
    ) {}
}
