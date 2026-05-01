<?php

use Aliziodev\Biteship\Support\CodFee;

test('calculate returns correct fee for jne 7 days', function () {
    // 4% dari 500.000 = 20.000, > min_fee 3.500
    expect(CodFee::calculate('jne', 500_000, '7_days'))->toBe(20_000);
});

test('calculate applies minimum fee when result is below minimum', function () {
    // 4% dari 50.000 = 2.000, < min_fee 3.500 → pakai min_fee
    expect(CodFee::calculate('jne', 50_000, '7_days'))->toBe(3_500);
});

test('calculate works for sicepat', function () {
    // 4% dari 200.000 = 8.000, > min_fee 2.000
    expect(CodFee::calculate('sicepat', 200_000, '7_days'))->toBe(8_000);
});

test('calculate works for different periods', function () {
    // JNE 5 days = 5%, JNE 3 days = 6%
    expect(CodFee::calculate('jne', 100_000, '5_days'))->toBe(5_000)
        ->and(CodFee::calculate('jne', 100_000, '3_days'))->toBe(6_000);
});

test('supports returns true for known couriers', function () {
    expect(CodFee::supports('jne'))->toBeTrue()
        ->and(CodFee::supports('sicepat'))->toBeTrue()
        ->and(CodFee::supports('anteraja'))->toBeTrue();
});

test('supports returns false for unknown courier', function () {
    expect(CodFee::supports('unknown_courier'))->toBeFalse();
});

test('maxValue returns correct limit', function () {
    expect(CodFee::maxValue('jne'))->toBe(5_000_000)
        ->and(CodFee::maxValue('sicepat'))->toBe(2_500_000)
        ->and(CodFee::maxValue('sap'))->toBe(10_000_000);
});

test('maxValue returns null for courier without limit', function () {
    expect(CodFee::maxValue('tiki'))->toBeNull()
        ->and(CodFee::maxValue('jnt'))->toBeNull();
});

test('throws for unknown courier', function () {
    expect(fn () => CodFee::calculate('unknown', 100_000))->toThrow(InvalidArgumentException::class);
});

test('throws for invalid period', function () {
    expect(fn () => CodFee::calculate('jne', 100_000, '1_day'))->toThrow(InvalidArgumentException::class);
});

test('supportedCouriers returns all couriers', function () {
    expect(CodFee::supportedCouriers())->toContain('jne', 'sicepat', 'sap', 'anteraja', 'tiki', 'jnt', 'id_express');
});
