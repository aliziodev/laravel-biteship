<?php

namespace Aliziodev\Biteship\Services;

use Aliziodev\Biteship\Contracts\BiteshipClientInterface;
use Aliziodev\Biteship\DTOs\Tracking\TrackingResponse;

class TrackingService
{
    public function __construct(
        private readonly BiteshipClientInterface $client,
    ) {}

    /**
     * Track via Biteship order ID.
     */
    public function trackByOrderId(string $orderId): TrackingResponse
    {
        $data = $this->client->get("/v1/trackings/{$orderId}");

        return TrackingResponse::fromArray($data);
    }

    /**
     * Track via waybill ID + courier code (public tracking — tidak perlu order dari Biteship).
     */
    public function trackByWaybill(string $waybillId, string $courierCode): TrackingResponse
    {
        $data = $this->client->get("/v1/trackings/{$waybillId}/couriers/{$courierCode}");

        return TrackingResponse::fromArray($data);
    }
}
