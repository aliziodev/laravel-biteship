<?php

use Aliziodev\Biteship\DTOs\Tracking\TrackingResponse;
use Aliziodev\Biteship\Facades\Biteship;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('trackByOrderId returns TrackingResponse with correct data', function () {
    Http::fake([
        'api.biteship.com/v1/trackings/ORD-123456/public' => Http::response($this->fixture('tracking_response'), 200),
    ]);

    $tracking = Biteship::tracking()->trackByOrderId('ORD-123456');

    expect($tracking)->toBeInstanceOf(TrackingResponse::class)
        ->and($tracking->status->value)->toBe('delivered')
        ->and($tracking->history)->toHaveCount(4);
});

test('trackByWaybill returns TrackingResponse for public tracking', function () {
    Http::fake([
        'api.biteship.com/v1/trackings/JNE00123456789/couriers/jne' => Http::response($this->fixture('tracking_response'), 200),
    ]);

    $tracking = Biteship::tracking()->trackByWaybill('JNE00123456789', 'jne');

    expect($tracking)->toBeInstanceOf(TrackingResponse::class)
        ->and($tracking->status->value)->toBe('delivered');
});

test('tracking history contains correct status flow', function () {
    Http::fake([
        'api.biteship.com/v1/trackings/ORD-123456/public' => Http::response($this->fixture('tracking_response'), 200),
    ]);

    $tracking = Biteship::tracking()->trackByOrderId('ORD-123456');

    $history = $tracking->history;

    expect($history->first()->status)->toBe('confirmed')
        ->and($history->get(1)->status)->toBe('picked')
        ->and($history->get(2)->status)->toBe('dropping_off')
        ->and($history->last()->status)->toBe('delivered');
});

test('tracking contains courier information', function () {
    Http::fake([
        'api.biteship.com/v1/trackings/ORD-123456/public' => Http::response($this->fixture('tracking_response'), 200),
    ]);

    $tracking = Biteship::tracking()->trackByOrderId('ORD-123456');

    expect($tracking->courier_company)->toBe('JNE')
        ->and($tracking->waybill_id)->toBe('JNE00123456789');
});
