<?php

namespace Aliziodev\Biteship\Exceptions;

class AuthenticationException extends BiteshipException
{
    public static function invalidApiKey(): static
    {
        return new static('Invalid Biteship API key. Check your BITESHIP_API_KEY.');
    }
}
