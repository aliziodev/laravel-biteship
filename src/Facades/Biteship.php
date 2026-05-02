<?php

namespace Aliziodev\Biteship\Facades;

use Aliziodev\Biteship\Services\CourierService;
use Aliziodev\Biteship\Services\DraftOrderService;
use Aliziodev\Biteship\Services\LabelService;
use Aliziodev\Biteship\Services\LocationService;
use Aliziodev\Biteship\Services\MapsService;
use Aliziodev\Biteship\Services\OrderService;
use Aliziodev\Biteship\Services\RateService;
use Aliziodev\Biteship\Services\TrackingService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static RateService rates()
 * @method static OrderService orders()
 * @method static TrackingService tracking()
 * @method static CourierService couriers()
 * @method static MapsService maps()
 * @method static LocationService locations()
 * @method static LabelService label()
 * @method static DraftOrderService draftOrders()
 *
 * @see \Aliziodev\Biteship\Biteship
 */
class Biteship extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'biteship';
    }
}
