<?php

use Aliziodev\Biteship\Exceptions\RateLimitException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Facades\Http;

test('retryAfter parses Retry-After header', function () {
    Http::fake([
        '*' => Http::response([], 429, ['Retry-After' => '30']),
    ]);

    $response = app(HttpFactory::class)->get('https://api.biteship.com/test');

    $exception = RateLimitException::tooManyRequests($response);

    expect($exception->retryAfter())->toBe(30)
        ->and($exception->getMessage())->toBe('Biteship rate limit exceeded.');
});

test('retryAfter returns null when header is missing', function () {
    Http::fake([
        '*' => Http::response([], 429),
    ]);

    $response = app(HttpFactory::class)->get('https://api.biteship.com/test');
    $exception = RateLimitException::tooManyRequests($response);

    expect($exception->retryAfter())->toBeNull();
});
