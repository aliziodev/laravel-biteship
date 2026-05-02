<?php

use Aliziodev\Biteship\DTOs\Tracking\TrackingResponse;
use Aliziodev\Biteship\Facades\Biteship;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('trackByOrderId returns TrackingResponse with correct data', function () {
    Http::fake([
        'api.biteship.com/v1/trackings/6251863341sa3714e6637fab' => Http::response(loadFixture('tracking_response'), 200),
    ]);

    $tracking = Biteship::tracking()->trackByOrderId('6251863341sa3714e6637fab');

    expect($tracking)->toBeInstanceOf(TrackingResponse::class)
        ->and($tracking->status->value)->toBe('delivered')
        ->and($tracking->history)->toHaveCount(6);
});

test('trackByWaybill returns TrackingResponse for public tracking', function () {
    Http::fake([
        'api.biteship.com/v1/trackings/0123082100003094/couriers/grab' => Http::response(loadFixture('tracking_response'), 200),
    ]);

    $tracking = Biteship::tracking()->trackByWaybill('0123082100003094', 'grab');

    expect($tracking)->toBeInstanceOf(TrackingResponse::class)
        ->and($tracking->status->value)->toBe('delivered');
});

test('tracking history contains correct status flow', function () {
    Http::fake([
        'api.biteship.com/v1/trackings/6251863341sa3714e6637fab' => Http::response(loadFixture('tracking_response'), 200),
    ]);

    $tracking = Biteship::tracking()->trackByOrderId('6251863341sa3714e6637fab');

    $history = $tracking->history;

    expect($history->first()->status)->toBe('confirmed')
        ->and($history->get(1)->status)->toBe('allocated')
        ->and($history->get(2)->status)->toBe('picking_up')
        ->and($history->last()->status)->toBe('delivered');
});

test('tracking contains courier information', function () {
    Http::fake([
        'api.biteship.com/v1/trackings/6251863341sa3714e6637fab' => Http::response(loadFixture('tracking_response'), 200),
    ]);

    $tracking = Biteship::tracking()->trackByOrderId('6251863341sa3714e6637fab');

    expect($tracking->courier_company)->toBe('grab')
        ->and($tracking->waybill_id)->toBe('0123082100003094')
        ->and($tracking->driver_name)->toBe('John Doe')
        ->and($tracking->driver_plate_number)->toBe('B 1234 ABC');
});
