<?php

use Aliziodev\Biteship\Facades\Biteship;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    Http::preventStrayRequests();
});

test('can create location', function () {
    Http::fake([
        'api.biteship.com/v1/locations' => Http::response(['success' => true, 'id' => 'loc_123', 'name' => 'Toko Pusat'], 200),
    ]);

    $response = Biteship::locations()->create([
        'name' => 'Toko Pusat',
        'contact_name' => 'Budi',
        'contact_phone' => '08123456789',
        'address' => 'Jl. Sudirman',
        'postal_code' => '12190',
    ]);

    expect($response)->toHaveKey('id', 'loc_123')
        ->and($response)->toHaveKey('name', 'Toko Pusat');
});

test('can find location by id', function () {
    Http::fake([
        'api.biteship.com/v1/locations/loc_123' => Http::response(['success' => true, 'id' => 'loc_123', 'name' => 'Toko Pusat'], 200),
    ]);

    $response = Biteship::locations()->find('loc_123');

    expect($response)->toHaveKey('id', 'loc_123')
        ->and($response)->toHaveKey('name', 'Toko Pusat');
});

test('can update location', function () {
    Http::fake([
        'api.biteship.com/v1/locations/loc_123' => Http::response(['success' => true, 'id' => 'loc_123', 'name' => 'Toko Baru'], 200),
    ]);

    $response = Biteship::locations()->update('loc_123', [
        'name' => 'Toko Baru',
    ]);

    expect($response)->toHaveKey('name', 'Toko Baru');

    // Pastikan HTTP POST yang dikirim (bukan PUT)
    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'locations/loc_123')
            && $request->method() === 'POST';
    });
});

test('can delete location', function () {
    Http::fake([
        'api.biteship.com/v1/locations/loc_123' => Http::response(['success' => true, 'message' => 'Deleted'], 200),
    ]);

    $response = Biteship::locations()->delete('loc_123');

    expect($response)->toHaveKey('success', true);

    Http::assertSent(function ($request) {
        return str_contains($request->url(), 'locations/loc_123')
            && $request->method() === 'DELETE';
    });
});
