<?php

use Aliziodev\Biteship\DTOs\Rate\RateRequest;
use Aliziodev\Biteship\DTOs\Rate\RateResponse;
use Aliziodev\Biteship\Exceptions\AuthenticationException;
use Aliziodev\Biteship\Exceptions\RateLimitException;
use Aliziodev\Biteship\Facades\Biteship;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

function rateRequest(): RateRequest
{
    return (new RateRequest)
        ->originAreaId('IDNP10001')
        ->originContact('Budi', '08123456789')
        ->originAddress('Jl. Sudirman No.1')
        ->destinationAreaId('IDNP20001')
        ->destinationContact('Ani', '08987654321')
        ->destinationAddress('Jl. Merdeka No.10')
        ->addItem(['name' => 'Baju', 'value' => 100000, 'weight' => 500, 'quantity' => 1]);
}

test('check returns RateResponse with pricing collection', function () {
    Http::fake([
        'api.biteship.com/v1/rates/couriers' => Http::response($this->fixture('rate_response'), 200),
    ]);

    $response = Biteship::rates()->check(rateRequest());

    expect($response)->toBeInstanceOf(RateResponse::class)
        ->and($response->success)->toBeTrue()
        ->and($response->pricing)->toHaveCount(3);
});

test('cheapest returns lowest price courier', function () {
    Http::fake([
        'api.biteship.com/v1/rates/couriers' => Http::response($this->fixture('rate_response'), 200),
    ]);

    $response = Biteship::rates()->check(rateRequest());
    $cheapest = $response->cheapest();

    expect($cheapest->price)->toBe(12000)
        ->and($cheapest->courierCode)->toBe('sicepat');
});

test('byCourier filters correctly', function () {
    Http::fake([
        'api.biteship.com/v1/rates/couriers' => Http::response($this->fixture('rate_response'), 200),
    ]);

    $response = Biteship::rates()->check(rateRequest());

    expect($response->byCourier('jne'))->toHaveCount(1)
        ->and($response->byCourier('unknown'))->toHaveCount(0);
});

test('codAvailable filters COD-capable couriers only', function () {
    Http::fake([
        'api.biteship.com/v1/rates/couriers' => Http::response($this->fixture('rate_response'), 200),
    ]);

    $response = Biteship::rates()->check(rateRequest());

    // Dari fixture: jne dan sicepat support COD, sap tidak
    expect($response->codAvailable())->toHaveCount(2);
});

test('cache prevents duplicate API call for same payload', function () {
    config(['biteship.cache.enabled' => true]);

    Http::fake([
        'api.biteship.com/v1/rates/couriers' => Http::sequence()
            ->push($this->fixture('rate_response'), 200)
            ->push($this->fixture('rate_response'), 200),
    ]);

    $request = rateRequest();
    $service = Biteship::rates();

    $service->check($request);
    $service->check($request); // second call — should hit cache

    Http::assertSentCount(1); // hanya 1 request ke API
});

test('fresh bypasses cache', function () {
    config(['biteship.cache.enabled' => true]);

    Http::fake([
        'api.biteship.com/v1/rates/couriers' => Http::sequence()
            ->push($this->fixture('rate_response'), 200)
            ->push($this->fixture('rate_response'), 200),
    ]);

    $request = rateRequest();
    $service = Biteship::rates();

    $service->check($request);
    $service->fresh()->check($request); // bypass cache

    Http::assertSentCount(2);
});

test('throws AuthenticationException on 401', function () {
    Http::fake([
        'api.biteship.com/v1/rates/couriers' => Http::response(['error' => 'Unauthorized'], 401),
    ]);

    expect(fn () => Biteship::rates()->check(rateRequest()))
        ->toThrow(AuthenticationException::class);
});

test('throws RateLimitException on 429', function () {
    Http::fake([
        'api.biteship.com/v1/rates/couriers' => Http::response([], 429, ['Retry-After' => '5']),
    ]);

    expect(fn () => Biteship::rates()->check(rateRequest()))
        ->toThrow(RateLimitException::class);
});
