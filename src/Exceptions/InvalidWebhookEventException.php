<?php

namespace Aliziodev\Biteship\Exceptions;

class InvalidWebhookEventException extends BiteshipException
{
    public function __construct(string $event)
    {
        parent::__construct("Unknown Biteship webhook event: \"{$event}\".");
    }
}
