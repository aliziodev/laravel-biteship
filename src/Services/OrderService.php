<?php

namespace Aliziodev\Biteship\Services;

use Aliziodev\Biteship\Contracts\BiteshipClientInterface;
use Aliziodev\Biteship\DTOs\Order\OrderRequest;
use Aliziodev\Biteship\DTOs\Order\OrderResponse;

class OrderService
{
    public function __construct(
        private readonly BiteshipClientInterface $client,
    ) {}

    /**
     * Buat order baru (langsung confirmed, bukan draft).
     */
    public function create(OrderRequest $request): OrderResponse
    {
        $data = $this->client->post('/v1/orders', $request->toArray());

        return OrderResponse::fromArray($data);
    }

    /**
     * Ambil detail order berdasarkan Biteship order ID.
     */
    public function find(string $orderId): OrderResponse
    {
        $data = $this->client->get("/v1/orders/{$orderId}");

        return OrderResponse::fromArray($data);
    }

    /**
     * Cancel order — gunakan endpoint POST /cancel (bukan DELETE, deprecated 2025).
     */
    public function cancel(string $orderId, ?string $reason = null): OrderResponse
    {
        $payload = $reason !== null ? ['cancellation_reason' => $reason] : [];

        $data = $this->client->post("/v1/orders/{$orderId}/cancel", $payload);

        return OrderResponse::fromArray($data);
    }

    /**
     * Update order (misal update contact/address sebelum pickup).
     */
    public function update(string $orderId, array $payload): OrderResponse
    {
        $data = $this->client->put("/v1/orders/{$orderId}", $payload);

        return OrderResponse::fromArray($data);
    }
}
