<?php

use Aliziodev\Biteship\Facades\Biteship;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('all returns collection of couriers', function () {
    Http::fake([
        'api.biteship.com/v1/couriers' => Http::response(loadFixture('couriers_response'), 200),
    ]);

    $couriers = Biteship::couriers()->all();

    expect($couriers)->toBeInstanceOf(Collection::class)
        ->and($couriers)->toHaveCount(5);
});

test('couriers contain correct structure', function () {
    Http::fake([
        'api.biteship.com/v1/couriers' => Http::response(loadFixture('couriers_response'), 200),
    ]);

    $couriers = Biteship::couriers()->all();

    $firstCourier = $couriers->first();

    expect($firstCourier)->toHaveKey('courier_code')
        ->and($firstCourier)->toHaveKey('courier_name')
        ->and($firstCourier)->toHaveKey('courier_service_code')
        ->and($firstCourier)->toHaveKey('courier_service_name')
        ->and($firstCourier['courier_name'])->toBe('JNE')
        ->and($firstCourier['courier_code'])->toBe('jne');
});

test('can filter couriers by code', function () {
    Http::fake([
        'api.biteship.com/v1/couriers' => Http::response(loadFixture('couriers_response'), 200),
    ]);

    $couriers = Biteship::couriers()->all();

    // There are 2 JNE services in our fixture
    $jneServices = $couriers->where('courier_code', 'jne');

    expect($jneServices)->toHaveCount(2)
        ->and($jneServices->first()['courier_name'])->toBe('JNE');
});

test('can group couriers by code', function () {
    Http::fake([
        'api.biteship.com/v1/couriers' => Http::response(loadFixture('couriers_response'), 200),
    ]);

    $couriers = Biteship::couriers()->all();
    $grouped = $couriers->groupBy('courier_code');

    expect($grouped)->toHaveCount(3)
        ->and($grouped->has('jne'))->toBeTrue()
        ->and($grouped->has('sicepat'))->toBeTrue()
        ->and($grouped->has('jnt'))->toBeTrue();
});
