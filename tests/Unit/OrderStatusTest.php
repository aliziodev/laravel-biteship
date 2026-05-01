<?php

use Aliziodev\Biteship\Enums\OrderStatus;

test('isFinal returns true for terminal statuses', function () {
    expect(OrderStatus::Delivered->isFinal())->toBeTrue()
        ->and(OrderStatus::Returned->isFinal())->toBeTrue()
        ->and(OrderStatus::Rejected->isFinal())->toBeTrue()
        ->and(OrderStatus::Disposed->isFinal())->toBeTrue()
        ->and(OrderStatus::CourierNotFound->isFinal())->toBeTrue()
        ->and(OrderStatus::Cancelled->isFinal())->toBeTrue();
});

test('isFinal returns false for in-progress statuses', function () {
    expect(OrderStatus::Confirmed->isFinal())->toBeFalse()
        ->and(OrderStatus::PickingUp->isFinal())->toBeFalse()
        ->and(OrderStatus::DroppingOff->isFinal())->toBeFalse()
        ->and(OrderStatus::OnHold->isFinal())->toBeFalse();
});

test('isSuccess returns true only for delivered', function () {
    expect(OrderStatus::Delivered->isSuccess())->toBeTrue();

    foreach (OrderStatus::cases() as $status) {
        if ($status !== OrderStatus::Delivered) {
            expect($status->isSuccess())->toBeFalse();
        }
    }
});

test('isProblem returns true only for on_hold and return_in_transit', function () {
    expect(OrderStatus::OnHold->isProblem())->toBeTrue()
        ->and(OrderStatus::ReturnInTransit->isProblem())->toBeTrue()
        ->and(OrderStatus::Confirmed->isProblem())->toBeFalse()
        ->and(OrderStatus::Delivered->isProblem())->toBeFalse();
});

test('canCancel returns true only for early statuses', function () {
    expect(OrderStatus::Confirmed->canCancel())->toBeTrue()
        ->and(OrderStatus::Scheduled->canCancel())->toBeTrue()
        ->and(OrderStatus::Allocated->canCancel())->toBeTrue()
        ->and(OrderStatus::PickingUp->canCancel())->toBeFalse()
        ->and(OrderStatus::Delivered->canCancel())->toBeFalse()
        ->and(OrderStatus::Cancelled->canCancel())->toBeFalse();
});

test('all 14 statuses exist', function () {
    expect(OrderStatus::cases())->toHaveCount(14);
});

test('label returns Indonesian string', function () {
    expect(OrderStatus::Delivered->label())->toBe('Berhasil Dikirim')
        ->and(OrderStatus::Cancelled->label())->toBe('Dibatalkan')
        ->and(OrderStatus::OnHold->label())->toBe('Ditahan');
});
