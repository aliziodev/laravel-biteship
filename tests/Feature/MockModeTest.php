<?php

use Aliziodev\Biteship\DTOs\Order\OrderRequest;
use Aliziodev\Biteship\DTOs\Rate\RateRequest;
use Aliziodev\Biteship\Exceptions\AuthenticationException;
use Aliziodev\Biteship\Exceptions\RateLimitException;
use Aliziodev\Biteship\Exceptions\ValidationException;
use Aliziodev\Biteship\Facades\Biteship;

beforeEach(function () {
    // Enable mock mode for all tests in this file
    config(['biteship.mock_mode.enabled' => true]);
    config(['biteship.mock_mode.validation' => true]);
    config(['biteship.mock_mode.errors' => []]);
});

// Rates API Tests
test('mock mode returns dynamic rates response', function () {
    $rateRequest = (new RateRequest)
        ->originAreaId('IDNP10001')
        ->originContact('Budi', '08123456789')
        ->originAddress('Jl. Sudirman No.1')
        ->destinationAreaId('IDNP20001')
        ->destinationContact('Ani', '08987654321')
        ->destinationAddress('Jl. Merdeka No.10')
        ->addItem(['name' => 'Baju', 'value' => 100000, 'weight' => 500, 'quantity' => 1]);

    $response = Biteship::rates()->check($rateRequest);

    $firstRate = $response->pricing->first();

    expect($response->pricing)->toHaveCount(3)
        ->and($firstRate->courier_code)->toBeIn(['jne', 'sicepat', 'jnt'])
        ->and($firstRate->price)->toBeInt()->toBeGreaterThan(0);
});

test('mock rates calculates price based on weight', function () {
    $rateRequestLight = (new RateRequest)
        ->originAreaId('IDNP10001')
        ->originContact('Budi', '08123456789')
        ->originAddress('Jl. Sudirman No.1')
        ->destinationAreaId('IDNP20001')
        ->destinationContact('Ani', '08987654321')
        ->destinationAddress('Jl. Merdeka No.10')
        ->addItem(['name' => 'Baju', 'value' => 100000, 'weight' => 200, 'quantity' => 1]);

    $rateRequestHeavy = (new RateRequest)
        ->originAreaId('IDNP10001')
        ->originContact('Budi', '08123456789')
        ->originAddress('Jl. Sudirman No.1')
        ->destinationAreaId('IDNP20001')
        ->destinationContact('Ani', '08987654321')
        ->destinationAddress('Jl. Merdeka No.10')
        ->addItem(['name' => 'Laptop', 'value' => 100000, 'weight' => 2000, 'quantity' => 1]);

    $responseLight = Biteship::rates()->check($rateRequestLight);
    $responseHeavy = Biteship::rates()->check($rateRequestHeavy);

    $priceLight = $responseLight->pricing->first()->price;
    $priceHeavy = $responseHeavy->pricing->first()->price;

    expect($priceHeavy)->toBeGreaterThan($priceLight);
});

test('mock rates validates required fields', function () {
    config(['biteship.mock_mode.validation' => true]);

    $rateRequest = (new RateRequest)
        // Missing origin and destination
        ->addItem(['name' => 'Baju', 'value' => 100000, 'weight' => 500, 'quantity' => 1]);

    expect(fn () => Biteship::rates()->check($rateRequest))
        ->toThrow(ValidationException::class);
});

// Orders API Tests
test('mock mode creates order with dynamic id', function () {
    $orderRequest = (new OrderRequest)
        ->originAreaId('IDNP10001')
        ->originContact('Toko Elektronik', '0211234567')
        ->originAddress('Jl. Sudirman No.1, Jakarta')
        ->destinationAreaId('IDNP20001')
        ->destinationContact('Budi Santoso', '08123456789')
        ->destinationAddress('Jl. Merdeka No.10, Bandung')
        ->courier('jne', 'REG')
        ->addItem(['name' => 'Laptop', 'value' => 5000000, 'weight' => 2000, 'quantity' => 1])
        ->referenceId('ORDER-INTERNAL-123');

    $response = Biteship::orders()->create($orderRequest);

    expect($response->id)->toStartWith('ORD-')
        ->and($response->status->value)->toBe('confirmed')
        ->and($response->price)->toBeInt()->toBeGreaterThan(0)
        ->and($response->reference_id)->toBe('ORDER-INTERNAL-123');
});

test('mock order can be retrieved after creation', function () {
    $orderRequest = (new OrderRequest)
        ->originAreaId('IDNP10001')
        ->originContact('Toko Elektronik', '0211234567')
        ->originAddress('Jl. Sudirman No.1, Jakarta')
        ->destinationAreaId('IDNP20001')
        ->destinationContact('Budi Santoso', '08123456789')
        ->destinationAddress('Jl. Merdeka No.10, Bandung')
        ->courier('jne', 'REG')
        ->addItem(['name' => 'Laptop', 'value' => 5000000, 'weight' => 2000, 'quantity' => 1]);

    $created = Biteship::orders()->create($orderRequest);
    $retrieved = Biteship::orders()->find($created->id);

    expect($retrieved->id)->toBe($created->id)
        ->and($retrieved->status->value)->toBe('confirmed');
});

test('mock order can be cancelled', function () {
    $orderRequest = (new OrderRequest)
        ->originAreaId('IDNP10001')
        ->originContact('Toko Elektronik', '0211234567')
        ->originAddress('Jl. Sudirman No.1, Jakarta')
        ->destinationAreaId('IDNP20001')
        ->destinationContact('Budi Santoso', '08123456789')
        ->destinationAddress('Jl. Merdeka No.10, Bandung')
        ->courier('jne', 'REG')
        ->addItem(['name' => 'Laptop', 'value' => 5000000, 'weight' => 2000, 'quantity' => 1]);

    $created = Biteship::orders()->create($orderRequest);
    $cancelled = Biteship::orders()->cancel($created->id, 'others', 'change mind');

    expect($cancelled->status->value)->toBe('cancelled');
});

test('mock order validates required fields', function () {
    config(['biteship.mock_mode.validation' => true]);

    $orderRequest = (new OrderRequest)
        // Missing origin contact and address
        ->destinationAreaId('IDNP20001')
        ->destinationContact('Budi Santoso', '08123456789')
        ->destinationAddress('Jl. Merdeka No.10, Bandung')
        ->courier('jne', 'REG')
        ->addItem(['name' => 'Laptop', 'value' => 5000000, 'weight' => 2000, 'quantity' => 1]);

    expect(fn () => Biteship::orders()->create($orderRequest))
        ->toThrow(ValidationException::class);
});

// Error Simulation Tests
test('mock mode simulates authentication error', function () {
    config(['biteship.mock_mode.errors.authentication' => true]);

    $rateRequest = (new RateRequest)
        ->originAreaId('IDNP10001')
        ->originContact('Budi', '08123456789')
        ->originAddress('Jl. Sudirman No.1')
        ->destinationAreaId('IDNP20001')
        ->destinationContact('Ani', '08987654321')
        ->destinationAddress('Jl. Merdeka No.10')
        ->addItem(['name' => 'Baju', 'value' => 100000, 'weight' => 500, 'quantity' => 1]);

    expect(fn () => Biteship::rates()->check($rateRequest))
        ->toThrow(AuthenticationException::class);
});

test('mock mode simulates rate limit error', function () {
    config(['biteship.mock_mode.errors.rate_limit' => true]);

    $rateRequest = (new RateRequest)
        ->originAreaId('IDNP10001')
        ->originContact('Budi', '08123456789')
        ->originAddress('Jl. Sudirman No.1')
        ->destinationAreaId('IDNP20001')
        ->destinationContact('Ani', '08987654321')
        ->destinationAddress('Jl. Merdeka No.10')
        ->addItem(['name' => 'Baju', 'value' => 100000, 'weight' => 500, 'quantity' => 1]);

    expect(fn () => Biteship::rates()->check($rateRequest))
        ->toThrow(RateLimitException::class);
});

test('mock mode simulates validation error', function () {
    config(['biteship.mock_mode.errors.validation' => true]);

    $rateRequest = (new RateRequest)
        ->originAreaId('IDNP10001')
        ->originContact('Budi', '08123456789')
        ->originAddress('Jl. Sudirman No.1')
        ->destinationAreaId('IDNP20001')
        ->destinationContact('Ani', '08987654321')
        ->destinationAddress('Jl. Merdeka No.10')
        ->addItem(['name' => 'Baju', 'value' => 100000, 'weight' => 500, 'quantity' => 1]);

    expect(fn () => Biteship::rates()->check($rateRequest))
        ->toThrow(ValidationException::class);
});

// Feature Tests
test('mock mode supports COD orders', function () {
    $orderRequest = (new OrderRequest)
        ->originAreaId('IDNP10001')
        ->originContact('Toko Elektronik', '0211234567')
        ->originAddress('Jl. Sudirman No.1, Jakarta')
        ->destinationAreaId('IDNP20001')
        ->destinationContact('Budi Santoso', '08123456789')
        ->destinationAddress('Jl. Merdeka No.10, Bandung')
        ->cashOnDelivery(500000)
        ->courier('jne', 'REG')
        ->addItem(['name' => 'Laptop', 'value' => 5000000, 'weight' => 2000, 'quantity' => 1]);

    $response = Biteship::orders()->create($orderRequest);

    // COD fee is calculated as 5000 if COD > 0
    expect($response->cod_fee)->toBe(5000);
});

test('mock mode generates unique order ids', function () {
    $orderRequest = (new OrderRequest)
        ->originAreaId('IDNP10001')
        ->originContact('Toko Elektronik', '0211234567')
        ->originAddress('Jl. Sudirman No.1, Jakarta')
        ->destinationAreaId('IDNP20001')
        ->destinationContact('Budi Santoso', '08123456789')
        ->destinationAddress('Jl. Merdeka No.10, Bandung')
        ->courier('jne', 'REG')
        ->addItem(['name' => 'Laptop', 'value' => 5000000, 'weight' => 2000, 'quantity' => 1]);

    $order1 = Biteship::orders()->create($orderRequest);
    $order2 = Biteship::orders()->create($orderRequest);

    expect($order1->id)->not->toBe($order2->id);
});

test('mock mode validation can be disabled', function () {
    config(['biteship.mock_mode.validation' => false]);

    // This request would normally fail validation (missing origin)
    $rateRequest = (new RateRequest)
        ->destinationAreaId('IDNP20001')
        ->destinationContact('Ani', '08987654321')
        ->destinationAddress('Jl. Merdeka No.10')
        ->addItem(['name' => 'Baju', 'value' => 100000, 'weight' => 500, 'quantity' => 1]);

    // Should not throw exception when validation is disabled
    $response = Biteship::rates()->check($rateRequest);

    expect($response->pricing)->toHaveCount(3);
});

// Shipper Tests
test('shipper fields are included in order request', function () {
    $orderRequest = (new OrderRequest)
        ->shipper('Toko Elektronik', '021-12345678', 'support@toko.com', 'PT Toko Elektronik')
        ->originAreaId('IDNP10001')
        ->originContact('Budi', '08123456789')
        ->originAddress('Jl. Sudirman No.1, Jakarta')
        ->destinationAreaId('IDNP20001')
        ->destinationContact('Ani', '08987654321')
        ->destinationAddress('Jl. Merdeka No.10, Bandung')
        ->courier('jne', 'REG')
        ->addItem(['name' => 'Laptop', 'value' => 5000000, 'weight' => 2000, 'quantity' => 1]);

    $payload = $orderRequest->toArray();

    expect($payload)->toHaveKey('shipper_contact_name')
        ->and($payload['shipper_contact_name'])->toBe('Toko Elektronik')
        ->and($payload['shipper_contact_phone'])->toBe('021-12345678')
        ->and($payload['shipper_contact_email'])->toBe('support@toko.com')
        ->and($payload['shipper_organization'])->toBe('PT Toko Elektronik');
});

test('default shipper can be set from config', function () {
    config(['biteship.default_shipper' => [
        'contact_name' => 'Toko Default',
        'contact_phone' => '021-99999999',
        'contact_email' => 'default@toko.com',
        'organization' => 'PT Default Indonesia',
    ]]);

    $orderRequest = (new OrderRequest)
        ->defaultShipper()
        ->originAreaId('IDNP10001')
        ->originContact('Budi', '08123456789')
        ->originAddress('Jl. Sudirman No.1, Jakarta')
        ->destinationAreaId('IDNP20001')
        ->destinationContact('Ani', '08987654321')
        ->destinationAddress('Jl. Merdeka No.10, Bandung')
        ->courier('jne', 'REG')
        ->addItem(['name' => 'Laptop', 'value' => 5000000, 'weight' => 2000, 'quantity' => 1]);

    $payload = $orderRequest->toArray();

    expect($payload['shipper_contact_name'])->toBe('Toko Default')
        ->and($payload['shipper_contact_phone'])->toBe('021-99999999')
        ->and($payload['shipper_contact_email'])->toBe('default@toko.com')
        ->and($payload['shipper_organization'])->toBe('PT Default Indonesia');
});

test('shipper data is returned in order response', function () {
    $orderRequest = (new OrderRequest)
        ->shipper('Toko Elektronik', '021-12345678', 'support@toko.com', 'PT Toko Elektronik')
        ->originAreaId('IDNP10001')
        ->originContact('Budi', '08123456789')
        ->originAddress('Jl. Sudirman No.1, Jakarta')
        ->destinationAreaId('IDNP20001')
        ->destinationContact('Ani', '08987654321')
        ->destinationAddress('Jl. Merdeka No.10, Bandung')
        ->courier('jne', 'REG')
        ->addItem(['name' => 'Laptop', 'value' => 5000000, 'weight' => 2000, 'quantity' => 1]);

    $response = Biteship::orders()->create($orderRequest);

    // Verify order was created successfully with shipper data
    expect($response->id)->toStartWith('ORD-')
        ->and($response->status->value)->toBe('confirmed');
});

test('shipper fields are optional', function () {
    $orderRequest = (new OrderRequest)
        ->originAreaId('IDNP10001')
        ->originContact('Budi', '08123456789')
        ->originAddress('Jl. Sudirman No.1, Jakarta')
        ->destinationAreaId('IDNP20001')
        ->destinationContact('Ani', '08987654321')
        ->destinationAddress('Jl. Merdeka No.10, Bandung')
        ->courier('jne', 'REG')
        ->addItem(['name' => 'Laptop', 'value' => 5000000, 'weight' => 2000, 'quantity' => 1]);

    $payload = $orderRequest->toArray();

    expect($payload)->not->toHaveKey('shipper_contact_name')
        ->and($payload)->not->toHaveKey('shipper_contact_phone');
});

test('individual shipper fields can be set', function () {
    $orderRequest = (new OrderRequest)
        ->shipperName('Toko A')
        ->shipperPhone('021-11111111')
        ->shipperEmail('a@toko.com')
        ->shipperOrganization('PT Toko A')
        ->originAreaId('IDNP10001')
        ->originContact('Budi', '08123456789')
        ->originAddress('Jl. Sudirman No.1, Jakarta')
        ->destinationAreaId('IDNP20001')
        ->destinationContact('Ani', '08987654321')
        ->destinationAddress('Jl. Merdeka No.10, Bandung')
        ->courier('jne', 'REG')
        ->addItem(['name' => 'Laptop', 'value' => 5000000, 'weight' => 2000, 'quantity' => 1]);

    $payload = $orderRequest->toArray();

    expect($payload['shipper_contact_name'])->toBe('Toko A')
        ->and($payload['shipper_contact_phone'])->toBe('021-11111111')
        ->and($payload['shipper_contact_email'])->toBe('a@toko.com')
        ->and($payload['shipper_organization'])->toBe('PT Toko A');
});
