<?php

namespace Aliziodev\Biteship\Exceptions;

use Illuminate\Http\Client\Response;

class RateLimitException extends BiteshipException
{
    public function __construct(
        string $message,
        private readonly ?int $retryAfter = null,
    ) {
        parent::__construct($message);
    }

    public static function tooManyRequests(Response $response): static
    {
        $retryAfter = ($val = $response->header('Retry-After')) ? (int) $val : null;

        return new static('Biteship rate limit exceeded.', $retryAfter);
    }

    public function retryAfter(): ?int
    {
        return $this->retryAfter;
    }
}
