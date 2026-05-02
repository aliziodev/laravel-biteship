<?php

namespace Aliziodev\Biteship;

use Aliziodev\Biteship\Contracts\BiteshipClientInterface;
use Aliziodev\Biteship\Services\CourierService;
use Aliziodev\Biteship\Services\DraftOrderService;
use Aliziodev\Biteship\Services\LabelService;
use Aliziodev\Biteship\Services\LocationService;
use Aliziodev\Biteship\Services\MapsService;
use Aliziodev\Biteship\Services\OrderService;
use Aliziodev\Biteship\Services\RateService;
use Aliziodev\Biteship\Services\TrackingService;

class Biteship
{
    public function __construct(
        private readonly BiteshipClientInterface $client,
    ) {}

    public function rates(): RateService
    {
        $cache = app('cache')->store(config('biteship.cache.store') ?: null);

        return new RateService($this->client, $cache);
    }

    public function orders(): OrderService
    {
        return new OrderService($this->client);
    }

    public function draftOrders(): DraftOrderService
    {
        return new DraftOrderService($this->client);
    }

    public function tracking(): TrackingService
    {
        return new TrackingService($this->client);
    }

    public function couriers(): CourierService
    {
        return new CourierService($this->client);
    }

    public function locations(): LocationService
    {
        return new LocationService($this->client);
    }

    public function maps(): MapsService
    {
        return new MapsService($this->client);
    }

    public function label(): LabelService
    {
        return new LabelService;
    }
}
