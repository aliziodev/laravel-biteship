<?php

namespace Aliziodev\Biteship\Exceptions;

use Illuminate\Http\Client\Response;

class ValidationException extends BiteshipException
{
    public function __construct(
        string $message,
        private readonly array $errors = [],
    ) {
        parent::__construct($message);
    }

    public static function fromResponse(Response $response): static
    {
        $body = $response->json();

        return new static(
            $body['error'] ?? 'Biteship validation error.',
            $body['details'] ?? [],
        );
    }

    public function errors(): array
    {
        return $this->errors;
    }
}
