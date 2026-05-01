<?php

namespace Aliziodev\Biteship\Exceptions;

use Illuminate\Http\Client\Response;

class ApiException extends BiteshipException
{
    public function __construct(
        string $message,
        private readonly int $statusCode = 0,
    ) {
        parent::__construct($message);
    }

    public static function fromResponse(Response $response): static
    {
        $body = $response->json();

        return new static(
            $body['error'] ?? "Biteship API error ({$response->status()}).",
            $response->status(),
        );
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }
}
