<?php

use Aliziodev\Biteship\Events\OrderStatusUpdated;
use Aliziodev\Biteship\Events\OrderWaybillUpdated;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake([
        OrderStatusUpdated::class,
        OrderPriceUpdated::class,
        OrderWaybillUpdated::class,
    ]);
});

test('valid order.status webhook dispatches OrderStatusUpdated', function () {
    $payload = $this->fixture('webhook_order_status');

    $this->postJson(config('biteship.webhook.path'), $payload)
        ->assertOk()
        ->assertJson(['received' => true]);

    Event::assertDispatched(OrderStatusUpdated::class, function ($event) {
        return $event->payload->orderId === 'ORD-123456';
    });
});

test('valid order.waybill_id webhook dispatches OrderWaybillUpdated', function () {
    $payload = $this->fixture('webhook_order_waybill');

    $this->postJson(config('biteship.webhook.path'), $payload)
        ->assertOk();

    Event::assertDispatched(OrderWaybillUpdated::class, function ($event) {
        return $event->payload->waybillId === 'JNE00123456789';
    });
});

test('unknown event returns 422 with message', function () {
    $this->postJson(config('biteship.webhook.path'), [
        'event' => 'order.unknown',
        'order_id' => 'ORD-123456',
    ])->assertStatus(422)->assertJsonStructure(['message']);

    Event::assertNothingDispatched();
});

test('invalid signature returns 401', function () {
    config([
        'biteship.webhook.signature_key' => 'X-My-Signature',
        'biteship.webhook.signature_secret' => 'correct-secret',
    ]);

    $this->postJson(
        config('biteship.webhook.path'),
        $this->fixture('webhook_order_status'),
        ['X-My-Signature' => 'wrong-secret']
    )->assertUnauthorized();

    Event::assertNothingDispatched();
});

test('valid signature passes verification', function () {
    config([
        'biteship.webhook.signature_key' => 'X-My-Signature',
        'biteship.webhook.signature_secret' => 'correct-secret',
    ]);

    $this->postJson(
        config('biteship.webhook.path'),
        $this->fixture('webhook_order_status'),
        ['X-My-Signature' => 'correct-secret']
    )->assertOk();

    Event::assertDispatched(OrderStatusUpdated::class);
});

test('missing signature header returns 401 when signature configured', function () {
    config([
        'biteship.webhook.signature_key' => 'X-My-Signature',
        'biteship.webhook.signature_secret' => 'correct-secret',
    ]);

    // No signature header
    $this->postJson(
        config('biteship.webhook.path'),
        $this->fixture('webhook_order_status'),
    )->assertUnauthorized();
});

test('no signature config skips verification', function () {
    config([
        'biteship.webhook.signature_key' => null,
        'biteship.webhook.signature_secret' => null,
    ]);

    $this->postJson(
        config('biteship.webhook.path'),
        $this->fixture('webhook_order_status'),
    )->assertOk();

    Event::assertDispatched(OrderStatusUpdated::class);
});
