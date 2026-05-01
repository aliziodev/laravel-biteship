<?php

use Aliziodev\Biteship\DTOs\Label\Label;
use Aliziodev\Biteship\Facades\Biteship;
use Illuminate\Http\Response;

test('fromRaw creates Label DTO from order response', function () {
    $rawOrder = [
        'id' => 'ORD-123456',
        'courier' => [
            'company' => 'JNE',
            'waybill_id' => 'JNE00123456789',
            'tracking_id' => 'JP1234567890',
        ],
        'origin' => [
            'contact_name' => 'Toko Elektronik',
            'address' => 'Jl. Teknologi No. 10, Jakarta',
        ],
        'destination' => [
            'contact_name' => 'Budi Santoso',
            'contact_phone' => '08123456789',
            'address' => 'Jl. Merdeka No. 20, Bandung',
            'cash_on_delivery' => 500000,
        ],
        'items' => [
            ['name' => 'Laptop', 'weight' => 2000, 'quantity' => 1],
        ],
    ];

    $label = Biteship::label()->fromRaw($rawOrder);

    expect($label)->toBeInstanceOf(Label::class)
        ->and($label->courier_name)->toBe('JNE')
        ->and($label->waybill_id)->toBe('JNE00123456789')
        ->and($label->sender_name)->toBe('Toko Elektronik')
        ->and($label->recipient_name)->toBe('Budi Santoso')
        ->and($label->recipient_phone)->toBe('08123456789')
        ->and($label->cod_amount)->toBe(500000);
});

test('render returns HTML string', function () {
    $rawOrder = [
        'id' => 'ORD-123456',
        'courier' => [
            'company' => 'JNE',
            'waybill_id' => 'JNE00123456789',
        ],
        'origin' => [
            'contact_name' => 'Toko Elektronik',
            'address' => 'Jl. Teknologi No. 10, Jakarta',
        ],
        'destination' => [
            'contact_name' => 'Budi Santoso',
            'contact_phone' => '08123456789',
            'address' => 'Jl. Merdeka No. 20, Bandung',
        ],
        'items' => [
            ['name' => 'Laptop', 'weight' => 2000, 'quantity' => 1],
        ],
    ];

    $label = Biteship::label()->fromRaw($rawOrder);
    $html = Biteship::label()->render($label);

    expect($html)->toBeString()
        ->and($html)->toContain('JNE')
        ->and($html)->toContain('JNE00123456789');
});

test('response returns HTTP response with correct headers', function () {
    $rawOrder = [
        'id' => 'ORD-123456',
        'courier' => [
            'company' => 'JNE',
            'waybill_id' => 'JNE00123456789',
        ],
        'origin' => [
            'contact_name' => 'Toko Elektronik',
            'address' => 'Jl. Teknologi No. 10, Jakarta',
        ],
        'destination' => [
            'contact_name' => 'Budi Santoso',
            'contact_phone' => '08123456789',
            'address' => 'Jl. Merdeka No. 20, Bandung',
        ],
        'items' => [
            ['name' => 'Laptop', 'weight' => 2000, 'quantity' => 1],
        ],
    ];

    $label = Biteship::label()->fromRaw($rawOrder);
    $response = Biteship::label()->response($label);

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->status())->toBe(200)
        ->and($response->headers->get('Content-Type'))->toContain('text/html');
});

test('label contains total weight calculation', function () {
    $rawOrder = [
        'id' => 'ORD-123456',
        'courier' => [
            'company' => 'JNE',
            'type' => 'REG',
            'waybill_id' => 'JNE00123456789',
        ],
        'origin' => [
            'contact_name' => 'Toko Elektronik',
            'contact_phone' => '021-12345678',
            'address' => 'Jl. Teknologi No. 10, Jakarta',
        ],
        'destination' => [
            'contact_name' => 'Budi Santoso',
            'contact_phone' => '08123456789',
            'address' => 'Jl. Merdeka No. 20, Bandung',
        ],
        'items' => [
            ['name' => 'Laptop', 'weight' => 2000, 'quantity' => 1],
            ['name' => 'Mouse', 'weight' => 200, 'quantity' => 2],
        ],
    ];

    $label = Biteship::label()->fromRaw($rawOrder);

    expect($label->total_weight)->toBe(2400); // 2000 + (200 * 2)
});
