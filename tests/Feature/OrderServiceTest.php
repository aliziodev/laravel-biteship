<?php

use Aliziodev\Biteship\DTOs\Order\OrderRequest;
use Aliziodev\Biteship\DTOs\Order\OrderResponse;
use Aliziodev\Biteship\Enums\OrderStatus;
use Aliziodev\Biteship\Facades\Biteship;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

function orderRequest(): OrderRequest
{
    return (new OrderRequest)
        ->originAreaId('IDNP10001')
        ->originContact('Ali Sender', '08123456789')
        ->originAddress('Jl. Sudirman No.1')
        ->destinationAreaId('IDNP20001')
        ->destinationContact('Ani Recipient', '08987654321')
        ->destinationAddress('Jl. Merdeka No.10')
        ->courier('jne', 'REG')
        ->addItem(['name' => 'Sepatu Olahraga', 'value' => 250000, 'weight' => 800, 'quantity' => 1]);
}

test('create returns OrderResponse with correct data', function () {
    Http::fake([
        'api.biteship.com/v1/orders' => Http::response($this->fixture('order_response'), 200),
    ]);

    $response = Biteship::orders()->create(orderRequest());

    expect($response)->toBeInstanceOf(OrderResponse::class)
        ->and($response->id)->toBe('ORD-123456')
        ->and($response->status)->toBe(OrderStatus::Confirmed)
        ->and($response->courierCompany)->toBe('jne')
        ->and($response->price)->toBe(18000);
});

test('find returns OrderResponse', function () {
    Http::fake([
        'api.biteship.com/v1/orders/ORD-123456' => Http::response($this->fixture('order_response'), 200),
    ]);

    $response = Biteship::orders()->find('ORD-123456');

    expect($response->id)->toBe('ORD-123456');
});

test('cancel sends POST to cancel endpoint', function () {
    $cancelledFixture = array_merge($this->fixture('order_response'), ['status' => 'cancelled']);

    Http::fake([
        'api.biteship.com/v1/orders/ORD-123456/cancel' => Http::response($cancelledFixture, 200),
    ]);

    $response = Biteship::orders()->cancel('ORD-123456', 'Pembeli membatalkan');

    expect($response->status)->toBe(OrderStatus::Cancelled);

    Http::assertSent(fn ($request) => str_contains($request->url(), '/cancel') && $request->method() === 'POST'
    );
});

test('isCancellable returns true for confirmed status', function () {
    Http::fake([
        'api.biteship.com/v1/orders' => Http::response($this->fixture('order_response'), 200),
    ]);

    $response = Biteship::orders()->create(orderRequest());

    expect($response->isCancellable())->toBeTrue();
});

test('raw response is stored in OrderResponse', function () {
    Http::fake([
        'api.biteship.com/v1/orders' => Http::response($this->fixture('order_response'), 200),
    ]);

    $response = Biteship::orders()->create(orderRequest());

    expect($response->raw)->toBeArray()
        ->toHaveKey('id', 'ORD-123456');
});
