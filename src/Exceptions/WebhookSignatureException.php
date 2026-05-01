<?php

namespace Aliziodev\Biteship\Exceptions;

class WebhookSignatureException extends BiteshipException
{
    public function __construct(string $message = 'Invalid webhook signature.')
    {
        parent::__construct($message);
    }
}
