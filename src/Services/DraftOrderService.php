<?php

namespace Aliziodev\Biteship\Services;

use Aliziodev\Biteship\Contracts\BiteshipClientInterface;
use Aliziodev\Biteship\DTOs\Order\DraftOrderResponse;
use Aliziodev\Biteship\DTOs\Order\OrderRequest;
use Aliziodev\Biteship\DTOs\Order\OrderResponse;
use Aliziodev\Biteship\DTOs\Rate\RateResponse;

class DraftOrderService
{
    public function __construct(
        private readonly BiteshipClientInterface $client,
    ) {}

    /**
     * Create a new draft order.
     */
    public function create(OrderRequest $request): DraftOrderResponse
    {
        $data = $this->client->post('/v1/draft_orders', $request->toArray());

        return DraftOrderResponse::fromArray($data);
    }

    /**
     * Retrieve draft order by ID.
     */
    public function find(string $id): DraftOrderResponse
    {
        $data = $this->client->get("/v1/draft_orders/{$id}");

        return DraftOrderResponse::fromArray($data);
    }

    /**
     * Retrieve draft order rates.
     */
    public function rates(string $id): RateResponse
    {
        $data = $this->client->get("/v1/draft_orders/{$id}/rates");

        return RateResponse::fromArray($data);
    }

    /**
     * Update draft order (e.g. set courier or update origin/destination).
     */
    public function update(string $id, array $payload): DraftOrderResponse
    {
        $data = $this->client->post("/v1/draft_orders/{$id}", $payload);

        return DraftOrderResponse::fromArray($data);
    }

    /**
     * Helper to set courier for draft order.
     */
    public function setCourier(string $id, string $company, string $type): DraftOrderResponse
    {
        return $this->update($id, [
            'courier_company' => $company,
            'courier_type' => $type,
        ]);
    }

    /**
     * Delete draft order.
     */
    public function delete(string $id): array
    {
        return $this->client->delete("/v1/draft_orders/{$id}");
    }

    /**
     * Confirm draft order to become a real order.
     */
    public function confirm(string $id): OrderResponse
    {
        $data = $this->client->post("/v1/draft_orders/{$id}/confirm", []);

        return OrderResponse::fromArray($data);
    }
}
