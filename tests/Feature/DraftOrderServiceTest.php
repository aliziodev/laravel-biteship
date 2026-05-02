<?php

namespace Tests\Feature;

use Aliziodev\Biteship\Contracts\BiteshipClientInterface;
use Aliziodev\Biteship\DTOs\Order\DraftOrderResponse;
use Aliziodev\Biteship\DTOs\Order\OrderRequest;
use Aliziodev\Biteship\DTOs\Order\OrderResponse;
use Aliziodev\Biteship\Enums\DraftOrderStatus;
use Aliziodev\Biteship\Exceptions\ValidationException;
use Aliziodev\Biteship\Facades\Biteship;
use Aliziodev\Biteship\Http\MockBiteshipClient;

beforeEach(function () {
    $this->app->bind(
        BiteshipClientInterface::class,
        MockBiteshipClient::class,
    );
});

test('create draft order returns DraftOrderResponse with placed status', function () {
    $request = (new OrderRequest)
        ->originContact('Draft', '000')
        ->originAddress('Draft Address')
        ->destinationContact('Draft Dest', '111')
        ->destinationAddress('Dest Address')
        ->addItem(['name' => 'Item', 'value' => 1000, 'weight' => 1000, 'quantity' => 1]);

    $response = Biteship::draftOrders()->create($request);

    expect($response)->toBeInstanceOf(DraftOrderResponse::class)
        ->and($response->id)->toStartWith('DRAFT-')
        ->and($response->status)->toBe(DraftOrderStatus::PLACED)
        ->and($response->courier_company)->toBeNull()
        ->and($response->courier_type)->toBeNull();
});

test('create draft order with courier returns ready status', function () {
    $request = (new OrderRequest)
        ->originContact('Draft', '000')
        ->originAddress('Draft Address')
        ->destinationContact('Draft Dest', '111')
        ->destinationAddress('Dest Address')
        ->courier('jne', 'reg')
        ->addItem(['name' => 'Item', 'value' => 1000, 'weight' => 1000, 'quantity' => 1]);

    $response = Biteship::draftOrders()->create($request);

    expect($response)->toBeInstanceOf(DraftOrderResponse::class)
        ->and($response->id)->toStartWith('DRAFT-')
        ->and($response->status)->toBe(DraftOrderStatus::READY)
        ->and($response->courier_company)->toBe('jne')
        ->and($response->courier_type)->toBe('reg');
});

test('can retrieve draft order', function () {
    $request = (new OrderRequest)
        ->originContact('Draft', '000')
        ->originAddress('Draft Address')
        ->destinationContact('Draft Dest', '111')
        ->destinationAddress('Dest Address')
        ->addItem(['name' => 'Item', 'value' => 1000, 'weight' => 1000, 'quantity' => 1]);

    $draft = Biteship::draftOrders()->create($request);
    $found = Biteship::draftOrders()->find($draft->id);

    expect($found->id)->toBe($draft->id)
        ->and($found->status)->toBe(DraftOrderStatus::PLACED);
});

test('can update draft order to ready status', function () {
    $request = (new OrderRequest)
        ->originContact('Draft', '000')
        ->originAddress('Draft Address')
        ->destinationContact('Draft Dest', '111')
        ->destinationAddress('Dest Address')
        ->addItem(['name' => 'Item', 'value' => 1000, 'weight' => 1000, 'quantity' => 1]);

    $draft = Biteship::draftOrders()->create($request);
    expect($draft->status)->toBe(DraftOrderStatus::PLACED);

    $updated = Biteship::draftOrders()->setCourier($draft->id, 'sicepat', 'reg');

    expect($updated->id)->toBe($draft->id)
        ->and($updated->status)->toBe(DraftOrderStatus::READY)
        ->and($updated->courier_company)->toBe('sicepat');
});

test('can confirm ready draft order', function () {
    $request = (new OrderRequest)
        ->originContact('Draft', '000')
        ->originAddress('Draft Address')
        ->destinationContact('Draft Dest', '111')
        ->destinationAddress('Dest Address')
        ->courier('jne', 'reg')
        ->addItem(['name' => 'Item', 'value' => 1000, 'weight' => 1000, 'quantity' => 1]);

    $draft = Biteship::draftOrders()->create($request);
    $order = Biteship::draftOrders()->confirm($draft->id);

    expect($order)->toBeInstanceOf(OrderResponse::class)
        ->and($order->id)->toStartWith('ORD-')
        ->and($order->status->value)->toBe('confirmed')
        ->and($order->raw['draft_order_id'])->toBe($draft->id);
});

test('cannot confirm placed draft order', function () {
    $request = (new OrderRequest)
        ->originContact('Draft', '000')
        ->originAddress('Draft Address')
        ->destinationContact('Draft Dest', '111')
        ->destinationAddress('Dest Address')
        ->addItem(['name' => 'Item', 'value' => 1000, 'weight' => 1000, 'quantity' => 1]);

    $draft = Biteship::draftOrders()->create($request);
    Biteship::draftOrders()->confirm($draft->id);
})->throws(ValidationException::class);

test('can delete draft order', function () {
    $response = Biteship::draftOrders()->delete('DRAFT-12345');

    expect($response['success'])->toBeTrue()
        ->and($response['message'])->toBe('Draft order deleted');
});

test('can retrieve draft order rates', function () {
    $rates = Biteship::draftOrders()->rates('DRAFT-12345');

    expect($rates->success)->toBeTrue()
        ->and($rates->pricing)->not->toBeEmpty();
});
