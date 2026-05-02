<?php

use Aliziodev\Biteship\Facades\Biteship;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('areas returns collection of areas', function () {
    Http::fake([
        'api.biteship.com/v1/maps/areas*' => Http::response(loadFixture('maps_areas_response'), 200),
    ]);

    $areas = Biteship::maps()->areas('Jakarta');

    expect($areas)->toBeInstanceOf(Collection::class)
        ->and($areas)->toHaveCount(3);
});

test('areas passes correct query parameters', function () {
    Http::fake([
        'api.biteship.com/v1/maps/areas*' => Http::response(loadFixture('maps_areas_response'), 200),
    ]);

    Biteship::maps()->areas('Jakarta Selatan', 'single');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'maps/areas')
            && str_contains($request->url(), 'countries')
            && str_contains($request->url(), 'input')
            && str_contains($request->url(), 'type');
    });
});

test('areas with type all returns all areas', function () {
    Http::fake([
        'api.biteship.com/v1/maps/areas*' => Http::response(loadFixture('maps_areas_response'), 200),
    ]);

    $areas = Biteship::maps()->areas('Jakarta', 'all');

    expect($areas)->toHaveCount(3);
});

test('areas returns areas with correct structure', function () {
    Http::fake([
        'api.biteship.com/v1/maps/areas*' => Http::response(loadFixture('maps_areas_response'), 200),
    ]);

    $areas = Biteship::maps()->areas('Jakarta');

    $firstArea = $areas->first();

    expect($firstArea)->toHaveKey('id')
        ->and($firstArea)->toHaveKey('name')
        ->and($firstArea)->toHaveKey('description')
        ->and($firstArea)->toHaveKey('province')
        ->and($firstArea)->toHaveKey('city')
        ->and($firstArea['name'])->toBe('Jakarta Selatan')
        ->and($firstArea['province'])->toBe('DKI Jakarta');
});

test('areas with empty query returns empty collection', function () {
    Http::fake([
        'api.biteship.com/v1/maps/areas*' => Http::response(['success' => true, 'areas' => []], 200),
    ]);

    $areas = Biteship::maps()->areas('');

    expect($areas)->toHaveCount(0);
});
