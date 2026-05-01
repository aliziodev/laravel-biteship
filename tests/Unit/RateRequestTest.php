<?php

use Aliziodev\Biteship\DTOs\Rate\RateRequest;

test('toArray includes required fields', function () {
    $request = (new RateRequest)
        ->originAreaId('IDNP10001')
        ->originContact('Budi', '08123456789')
        ->originAddress('Jl. Sudirman No.1')
        ->destinationAreaId('IDNP20001')
        ->destinationContact('Ani', '08987654321')
        ->destinationAddress('Jl. Merdeka No.10')
        ->addItem(['name' => 'Baju', 'value' => 100000, 'weight' => 500, 'quantity' => 1]);

    $payload = $request->toArray();

    expect($payload)
        ->toHaveKey('origin_area_id', 'IDNP10001')
        ->toHaveKey('origin_contact_name', 'Budi')
        ->toHaveKey('destination_area_id', 'IDNP20001')
        ->toHaveKey('couriers', 'biteship')
        ->toHaveKey('items');

    expect($payload['items'])->toHaveCount(1);
});

test('couriers accepts array and converts to csv', function () {
    $request = (new RateRequest)->couriers(['jne', 'sicepat', 'anteraja']);
    $payload = $request->toArray();

    expect($payload['couriers'])->toBe('jne,sicepat,anteraja');
});

test('deliverScheduled sets delivery type and date', function () {
    $request = (new RateRequest)->deliverScheduled('2026-05-10', '09:00');
    $payload = $request->toArray();

    expect($payload['delivery_type'])->toBe('scheduled')
        ->and($payload['delivery_date'])->toBe('2026-05-10')
        ->and($payload['delivery_time'])->toBe('09:00');
});

test('optional note is included when set', function () {
    $request = (new RateRequest)
        ->originAddress('Jl. A', 'Depan warung')
        ->destinationAddress('Jl. B', 'Lantai 3');

    $payload = $request->toArray();

    expect($payload['origin_note'])->toBe('Depan warung')
        ->and($payload['destination_note'])->toBe('Lantai 3');
});
