<?php

use Aliziodev\Biteship\Enums\OrderStatus;
use Aliziodev\Biteship\Events\OrderPriceUpdated;
use Aliziodev\Biteship\Events\OrderStatusUpdated;
use Aliziodev\Biteship\Events\OrderWaybillUpdated;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    Event::fake();
});

test('OrderStatusPayload handles flat structure', function () {
    $payload = [
        'event' => 'order.status',
        'order_id' => 'order_123',
        'status' => 'confirmed',
        'courier_waybill_id' => 'WAYBILL123',
        'courier_tracking_id' => 'TRACK123',
    ];

    $this->postJson(config('biteship.webhook.path'), $payload)->assertOk();

    Event::assertDispatched(OrderStatusUpdated::class, function ($event) {
        return $event->payload->order_id === 'order_123'
            && $event->payload->status === OrderStatus::Confirmed
            && $event->payload->waybill_id === 'WAYBILL123'
            && $event->payload->courier_tracking_id === 'TRACK123';
    });
});

test('OrderPricePayload handles flat structure', function () {
    $payload = [
        'event' => 'order.price',
        'order_id' => 'order_123',
        'order_price' => 100000,
        'insurance_fee' => 5000,
    ];

    $this->postJson(config('biteship.webhook.path'), $payload)->assertOk();

    Event::assertDispatched(OrderPriceUpdated::class, function ($event) {
        return $event->payload->order_id === 'order_123'
            && $event->payload->price === 100000
            && $event->payload->insurance_fee === 5000;
    });
});

test('OrderWaybillPayload handles flat structure', function () {
    $payload = [
        'event' => 'order.waybill_id',
        'order_id' => 'order_123',
        'courier_waybill_id' => 'WAYBILL_NEW',
        'courier_tracking_id' => 'TRACK_NEW',
    ];

    $this->postJson(config('biteship.webhook.path'), $payload)->assertOk();

    Event::assertDispatched(OrderWaybillUpdated::class, function ($event) {
        return $event->payload->order_id === 'order_123'
            && $event->payload->waybill_id === 'WAYBILL_NEW'
            && $event->payload->courier_tracking_id === 'TRACK_NEW';
    });
});
