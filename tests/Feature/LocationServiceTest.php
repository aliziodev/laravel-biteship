<?php

use Aliziodev\Biteship\Facades\Biteship;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('search returns collection of areas', function () {
    Http::fake([
        'api.biteship.com/v1/maps/areas*' => Http::response($this->fixture('maps_areas_response'), 200),
    ]);

    $areas = Biteship::locations()->search('Jakarta');

    expect($areas)->toBeInstanceOf(Collection::class)
        ->and($areas)->toHaveCount(3);
});

test('search passes correct query parameters', function () {
    Http::fake([
        'api.biteship.com/v1/maps/areas*' => Http::response($this->fixture('maps_areas_response'), 200),
    ]);

    Biteship::locations()->search('Jakarta Selatan', 'single');

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'maps/areas')
            && str_contains($request->url(), 'countries')
            && str_contains($request->url(), 'input')
            && str_contains($request->url(), 'type');
    });
});

test('search with type all returns all areas', function () {
    Http::fake([
        'api.biteship.com/v1/maps/areas*' => Http::response($this->fixture('maps_areas_response'), 200),
    ]);

    $areas = Biteship::locations()->search('Jakarta', 'all');

    expect($areas)->toHaveCount(3);
});

test('search returns areas with correct structure', function () {
    Http::fake([
        'api.biteship.com/v1/maps/areas*' => Http::response($this->fixture('maps_areas_response'), 200),
    ]);

    $areas = Biteship::locations()->search('Jakarta');

    $firstArea = $areas->first();

    expect($firstArea)->toHaveKey('id')
        ->and($firstArea)->toHaveKey('name')
        ->and($firstArea)->toHaveKey('description')
        ->and($firstArea)->toHaveKey('province')
        ->and($firstArea)->toHaveKey('city')
        ->and($firstArea['name'])->toBe('Jakarta Selatan')
        ->and($firstArea['province'])->toBe('DKI Jakarta');
});

test('search with empty query returns empty collection', function () {
    Http::fake([
        'api.biteship.com/v1/maps/areas*' => Http::response(['success' => true, 'areas' => []], 200),
    ]);

    $areas = Biteship::locations()->search('');

    expect($areas)->toHaveCount(0);
});
