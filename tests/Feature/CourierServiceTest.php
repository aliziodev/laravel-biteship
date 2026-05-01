<?php

use Aliziodev\Biteship\Facades\Biteship;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('all returns collection of couriers', function () {
    Http::fake([
        'api.biteship.com/v1/couriers' => Http::response($this->fixture('couriers_response'), 200),
    ]);

    $couriers = Biteship::couriers()->all();

    expect($couriers)->toBeInstanceOf(Collection::class)
        ->and($couriers)->toHaveCount(3);
});

test('couriers contain correct structure', function () {
    Http::fake([
        'api.biteship.com/v1/couriers' => Http::response($this->fixture('couriers_response'), 200),
    ]);

    $couriers = Biteship::couriers()->all();

    $firstCourier = $couriers->first();

    expect($firstCourier)->toHaveKey('id')
        ->and($firstCourier)->toHaveKey('name')
        ->and($firstCourier)->toHaveKey('code')
        ->and($firstCourier)->toHaveKey('logo_url')
        ->and($firstCourier)->toHaveKey('services')
        ->and($firstCourier['name'])->toBe('JNE')
        ->and($firstCourier['code'])->toBe('jne');
});

test('couriers contain services array', function () {
    Http::fake([
        'api.biteship.com/v1/couriers' => Http::response($this->fixture('couriers_response'), 200),
    ]);

    $couriers = Biteship::couriers()->all();

    $jne = $couriers->firstWhere('code', 'jne');

    expect($jne['services'])->toBeArray()
        ->and($jne['services'])->toHaveCount(2)
        ->and($jne['services'][0]['code'])->toBe('REG')
        ->and($jne['services'][1]['code'])->toBe('YES');
});

test('can filter couriers by code', function () {
    Http::fake([
        'api.biteship.com/v1/couriers' => Http::response($this->fixture('couriers_response'), 200),
    ]);

    $couriers = Biteship::couriers()->all();
    $sicepat = $couriers->firstWhere('code', 'sicepat');

    expect($sicepat)->not->toBeNull()
        ->and($sicepat['name'])->toBe('SiCepat');
});

test('can group couriers by code', function () {
    Http::fake([
        'api.biteship.com/v1/couriers' => Http::response($this->fixture('couriers_response'), 200),
    ]);

    $couriers = Biteship::couriers()->all();
    $grouped = $couriers->groupBy('code');

    expect($grouped)->toHaveCount(3)
        ->and($grouped->has('jne'))->toBeTrue()
        ->and($grouped->has('sicepat'))->toBeTrue()
        ->and($grouped->has('jnt'))->toBeTrue();
});
