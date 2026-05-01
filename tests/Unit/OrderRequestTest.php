<?php

use Aliziodev\Biteship\DTOs\Order\OrderRequest;

test('can build order request with origin area id', function () {
    $request = (new OrderRequest)
        ->originAreaId('IDNP10001')
        ->originContact('Budi', '08123456789')
        ->originAddress('Jl. Sudirman No.1');

    $array = $request->toArray();

    expect($array['origin_area_id'])->toBe('IDNP10001')
        ->and($array['origin_contact_name'])->toBe('Budi')
        ->and($array['origin_contact_phone'])->toBe('08123456789')
        ->and($array['origin_address'])->toBe('Jl. Sudirman No.1');
});

test('can build order request with origin postal code', function () {
    $request = (new OrderRequest)
        ->originPostalCode('12440')
        ->originContact('Budi', '08123456789')
        ->originAddress('Jl. Sudirman No.1');

    $array = $request->toArray();

    expect($array['origin_postal_code'])->toBe('12440');
});

test('can build order request with destination area id', function () {
    $request = (new OrderRequest)
        ->destinationAreaId('IDNP20001')
        ->destinationContact('Ani', '08987654321')
        ->destinationAddress('Jl. Merdeka No.10');

    $array = $request->toArray();

    expect($array['destination_area_id'])->toBe('IDNP20001')
        ->and($array['destination_contact_name'])->toBe('Ani')
        ->and($array['destination_contact_phone'])->toBe('08987654321');
});

test('can build order request with COD', function () {
    $request = (new OrderRequest)
        ->destinationAreaId('IDNP20001')
        ->destinationContact('Ani', '08987654321')
        ->destinationAddress('Jl. Merdeka No.10')
        ->cashOnDelivery(500000);

    $array = $request->toArray();

    expect($array['destination_cash_on_delivery'])->toBe(500000);
});

test('can build order request with courier', function () {
    $request = (new OrderRequest)
        ->courier('jne', 'REG');

    $array = $request->toArray();

    expect($array['courier_company'])->toBe('jne')
        ->and($array['courier_type'])->toBe('REG');
});

test('can build order request with courier insurance', function () {
    $request = (new OrderRequest)
        ->courier('jne', 'REG', 'yes');

    $array = $request->toArray();

    expect($array['courier_insurance'])->toBe('yes');
});

test('can add single item', function () {
    $request = (new OrderRequest)
        ->addItem(['name' => 'Baju', 'value' => 100000, 'weight' => 500, 'quantity' => 1]);

    $array = $request->toArray();

    expect($array['items'])->toHaveCount(1)
        ->and($array['items'][0]['name'])->toBe('Baju')
        ->and($array['items'][0]['value'])->toBe(100000)
        ->and($array['items'][0]['weight'])->toBe(500)
        ->and($array['items'][0]['quantity'])->toBe(1);
});

test('can add multiple items', function () {
    $request = (new OrderRequest)
        ->addItem(['name' => 'Baju', 'value' => 100000, 'weight' => 500, 'quantity' => 1])
        ->addItem(['name' => 'Celana', 'value' => 150000, 'weight' => 600, 'quantity' => 2]);

    $array = $request->toArray();

    expect($array['items'])->toHaveCount(2);
});

test('can set items array', function () {
    $items = [
        ['name' => 'Baju', 'value' => 100000, 'weight' => 500, 'quantity' => 1],
        ['name' => 'Celana', 'value' => 150000, 'weight' => 600, 'quantity' => 2],
    ];

    $request = (new OrderRequest)->items($items);

    $array = $request->toArray();

    expect($array['items'])->toHaveCount(2);
});

test('can set reference id', function () {
    $request = (new OrderRequest)
        ->referenceId('ORD-INTERNAL-123');

    $array = $request->toArray();

    expect($array['reference_id'])->toBe('ORD-INTERNAL-123');
});

test('can set notes', function () {
    $request = (new OrderRequest)
        ->notes('Barang pecah belah, handle with care');

    $array = $request->toArray();

    expect($array['notes'])->toBe('Barang pecah belah, handle with care');
});

test('can set origin email', function () {
    $request = (new OrderRequest)
        ->originContact('Budi', '08123456789', 'budi@example.com')
        ->originAddress('Jl. Sudirman No.1');

    $array = $request->toArray();

    expect($array['origin_contact_email'])->toBe('budi@example.com');
});

test('can set destination email', function () {
    $request = (new OrderRequest)
        ->destinationContact('Ani', '08987654321', 'ani@example.com')
        ->destinationAddress('Jl. Merdeka No.10');

    $array = $request->toArray();

    expect($array['destination_contact_email'])->toBe('ani@example.com');
});

test('can set origin note', function () {
    $request = (new OrderRequest)
        ->originContact('Budi', '08123456789')
        ->originAddress('Jl. Sudirman No.1', 'Gedung A Lt 2');

    $array = $request->toArray();

    expect($array['origin_note'])->toBe('Gedung A Lt 2');
});

test('can set destination note', function () {
    $request = (new OrderRequest)
        ->destinationContact('Ani', '08987654321')
        ->destinationAddress('Jl. Merdeka No.10', 'Rumah pagar hitam');

    $array = $request->toArray();

    expect($array['destination_note'])->toBe('Rumah pagar hitam');
});

test('builder pattern allows chaining', function () {
    $request = (new OrderRequest)
        ->originAreaId('IDNP10001')
        ->originContact('Budi', '08123456789')
        ->originAddress('Jl. Sudirman No.1')
        ->destinationAreaId('IDNP20001')
        ->destinationContact('Ani', '08987654321')
        ->destinationAddress('Jl. Merdeka No.10')
        ->courier('jne', 'REG')
        ->addItem(['name' => 'Baju', 'value' => 100000, 'weight' => 500, 'quantity' => 1])
        ->referenceId('ORD-INTERNAL-123')
        ->notes('Handle with care');

    $array = $request->toArray();

    expect($array['origin_area_id'])->toBe('IDNP10001')
        ->and($array['destination_area_id'])->toBe('IDNP20001')
        ->and($array['courier_company'])->toBe('jne')
        ->and($array['items'])->toHaveCount(1)
        ->and($array['reference_id'])->toBe('ORD-INTERNAL-123')
        ->and($array['notes'])->toBe('Handle with care');
});
