<?php

namespace Aliziodev\Biteship\Http;

use Aliziodev\Biteship\Contracts\BiteshipClientInterface;
use Aliziodev\Biteship\Exceptions\ApiException;
use Aliziodev\Biteship\Exceptions\AuthenticationException;
use Aliziodev\Biteship\Exceptions\RateLimitException;
use Aliziodev\Biteship\Exceptions\ValidationException;
use Illuminate\Support\Str;

class MockBiteshipClient implements BiteshipClientInterface
{
    public function get(string $uri, array $query = []): array
    {
        $this->simulateDelay();
        $this->checkErrorSimulation();

        if (str_contains($uri, 'rates')) {
            return $this->mockRatesResponse($query);
        }

        // Check for specific order GET endpoint pattern: /v1/orders/{orderId}
        if (preg_match('/\/v1\/orders\/[^\/]+$/', $uri)) {
            return $this->mockGetOrderResponse($uri);
        }

        // Tracking by order ID: /v1/trackings/{orderId}/public
        if (str_contains($uri, 'trackings') && str_contains($uri, 'public')) {
            return $this->mockTrackingResponse();
        }

        // Tracking by waybill: /v1/trackings/{waybillId}/couriers/{courierCode}
        if (str_contains($uri, 'trackings') && str_contains($uri, 'couriers')) {
            return $this->mockTrackingResponse();
        }

        // Couriers list: /v1/couriers
        if (str_contains($uri, '/v1/couriers')) {
            return $this->mockCouriersResponse();
        }

        // Location search: /v1/maps/areas
        if (str_contains($uri, 'maps/areas')) {
            return $this->mockAreasResponse($query);
        }

        return ['success' => true];
    }

    public function post(string $uri, array $data = []): array
    {
        $this->simulateDelay();
        $this->checkErrorSimulation();

        // Check more specific URIs first
        if (str_contains($uri, 'cancel')) {
            return $this->mockCancelOrderResponse($uri);
        }

        if (str_contains($uri, 'rates')) {
            return $this->mockRatesResponse($data);
        }

        if (str_contains($uri, 'orders')) {
            return $this->mockCreateOrderResponse($data);
        }

        return ['success' => true];
    }

    public function put(string $uri, array $data = []): array
    {
        $this->simulateDelay();
        $this->checkErrorSimulation();

        return ['success' => true];
    }

    public function delete(string $uri): array
    {
        $this->simulateDelay();
        $this->checkErrorSimulation();

        return ['success' => true];
    }

    private function mockRatesResponse(array $data): array
    {
        if (config('biteship.mock_mode.validation', true)) {
            $this->validateRatesInput($data);
        }

        $basePrice = $this->calculateMockPrice($data['items'] ?? []);

        return [
            'success' => true,
            'pricing' => [
                [
                    'courier_code' => 'jne',
                    'courier_name' => 'JNE',
                    'courier_service_name' => 'JNE Regular',
                    'courier_service_code' => 'REG',
                    'price' => $basePrice,
                    'duration' => 2,
                    'shipment_duration_range' => '1-2 days',
                    'available_for_cash_on_delivery' => true,
                ],
                [
                    'courier_code' => 'sicepat',
                    'courier_name' => 'SiCepat',
                    'courier_service_name' => 'SiCepat BEST',
                    'courier_service_code' => 'BEST',
                    'price' => (int) ($basePrice * 0.85),
                    'duration' => 1,
                    'shipment_duration_range' => '1 day',
                    'available_for_cash_on_delivery' => true,
                ],
                [
                    'courier_code' => 'jnt',
                    'courier_name' => 'J&T Express',
                    'courier_service_name' => 'J&T EZ',
                    'courier_service_code' => 'EZ',
                    'price' => (int) ($basePrice * 0.90),
                    'duration' => 2,
                    'shipment_duration_range' => '1-2 days',
                    'available_for_cash_on_delivery' => false,
                ],
            ],
        ];
    }

    private function mockCreateOrderResponse(array $data): array
    {
        if (config('biteship.mock_mode.validation', true)) {
            $this->validateOrderInput($data);
        }

        $orderId = 'ORD-'.strtoupper(Str::random(10));
        $price = $this->calculateMockPrice($data['items'] ?? []);

        $response = [
            'success' => true,
            'id' => $orderId,
            'reference_id' => $data['reference_id'] ?? null,
            'status' => 'confirmed',
            'price' => $price,
            'courier' => [
                'company' => $data['courier_company'] ?? 'jne',
                'type' => $data['courier_type'] ?? 'REG',
                'waybill_id' => null,
                'tracking_id' => null,
            ],
            'shipper' => [
                'contact_name' => $data['shipper_contact_name'] ?? $data['origin_contact_name'] ?? '',
                'contact_phone' => $data['shipper_contact_phone'] ?? $data['origin_contact_phone'] ?? '',
                'contact_email' => $data['shipper_contact_email'] ?? null,
                'organization' => $data['shipper_organization'] ?? null,
            ],
            'origin' => [
                'contact_name' => $data['origin_contact_name'] ?? '',
                'contact_phone' => $data['origin_contact_phone'] ?? '',
                'contact_email' => $data['origin_contact_email'] ?? null,
                'address' => $data['origin_address'] ?? '',
                'note' => $data['origin_note'] ?? null,
                'area_id' => $data['origin_area_id'] ?? null,
                'postal_code' => $data['origin_postal_code'] ?? null,
            ],
            'destination' => [
                'contact_name' => $data['destination_contact_name'] ?? '',
                'contact_phone' => $data['destination_contact_phone'] ?? '',
                'contact_email' => $data['destination_contact_email'] ?? null,
                'address' => $data['destination_address'] ?? '',
                'note' => $data['destination_note'] ?? null,
                'area_id' => $data['destination_area_id'] ?? null,
                'postal_code' => $data['destination_postal_code'] ?? null,
                'cash_on_delivery' => $data['destination_cash_on_delivery'] ?? 0,
                'cash_on_delivery_fee' => ($data['destination_cash_on_delivery'] ?? 0) > 0 ? 5000 : 0,
            ],
            'items' => $data['items'] ?? [],
            'note' => $data['notes'] ?? null,
            'created_at' => now()->toIso8601String(),
        ];

        // Store mock order in cache for later retrieval
        cache()->put("mock_biteship_order_{$orderId}", $response, now()->addDays(7));

        return $response;
    }

    private function mockGetOrderResponse(string $uri): array
    {
        $orderId = $this->extractOrderIdFromUri($uri);

        if (! $orderId) {
            throw new ValidationException('Invalid order ID');
        }

        $cachedOrder = cache()->get("mock_biteship_order_{$orderId}");

        if ($cachedOrder) {
            return $cachedOrder;
        }

        // Return fallback mock data if not in cache
        return [
            'success' => true,
            'id' => $orderId,
            'status' => 'confirmed',
            'price' => 18000,
            'courier' => [
                'company' => 'jne',
                'type' => 'REG',
                'waybill_id' => 'JNE'.strtoupper(Str::random(8)),
                'tracking_id' => null,
            ],
        ];
    }

    private function mockCancelOrderResponse(string $uri): array
    {
        $orderId = $this->extractOrderIdFromUri($uri);

        if (! $orderId) {
            throw new ValidationException('Invalid order ID');
        }

        $cachedOrder = cache()->get("mock_biteship_order_{$orderId}");

        if ($cachedOrder) {
            $cachedOrder['status'] = 'cancelled';
            cache()->put("mock_biteship_order_{$orderId}", $cachedOrder, now()->addDays(7));
        }

        return [
            'success' => true,
            'id' => $orderId,
            'status' => 'cancelled',
            'message' => 'Order cancelled successfully',
        ];
    }

    private function mockTrackingResponse(): array
    {
        return [
            'success' => true,
            'id' => 'ORD-MOCK',
            'status' => 'confirmed',
            'history' => [
                [
                    'status' => 'confirmed',
                    'note' => 'Order dikonfirmasi (mock)',
                    'service_type' => 'regular',
                    'updated_at' => now()->toIso8601String(),
                ],
            ],
            'courier' => [
                'company' => 'jne',
                'type' => 'REG',
                'waybill_id' => null,
                'tracking_id' => null,
            ],
        ];
    }

    private function mockCouriersResponse(): array
    {
        $base = 'https://biteship.com/_next/image?url=%2Fimages%2Flanding%2F';
        $suffix = '.webp&w=256&q=75';

        return [
            'success' => true,
            'couriers' => [
                ['id' => 'jne',           'name' => 'JNE',               'code' => 'jne',           'logo_url' => $base.'jne'.$suffix,          'services' => [['code' => 'REG', 'name' => 'JNE Regular', 'type' => 'regular'], ['code' => 'YES', 'name' => 'JNE YES', 'type' => 'express'], ['code' => 'OKE', 'name' => 'JNE OKE', 'type' => 'regular']]],
                ['id' => 'jnt',           'name' => 'J&T Express',       'code' => 'jnt',           'logo_url' => $base.'jnt'.$suffix,          'services' => [['code' => 'EZ', 'name' => 'J&T EZ', 'type' => 'regular']]],
                ['id' => 'sicepat',       'name' => 'SiCepat',           'code' => 'sicepat',       'logo_url' => $base.'sicepat'.$suffix,      'services' => [['code' => 'BEST', 'name' => 'SiCepat BEST', 'type' => 'regular'], ['code' => 'GOKIL', 'name' => 'SiCepat Gokil', 'type' => 'regular']]],
                ['id' => 'tiki',          'name' => 'TIKI',              'code' => 'tiki',          'logo_url' => $base.'tiki'.$suffix,         'services' => [['code' => 'REG', 'name' => 'TIKI Regular', 'type' => 'regular'], ['code' => 'ONS', 'name' => 'TIKI ONS', 'type' => 'express']]],
                ['id' => 'ninja_xpress',  'name' => 'Ninja Xpress',      'code' => 'ninja_xpress',  'logo_url' => $base.'ninja'.$suffix,        'services' => [['code' => 'STD', 'name' => 'Ninja Standard', 'type' => 'regular']]],
                ['id' => 'id_express',    'name' => 'ID Express',        'code' => 'id_express',    'logo_url' => $base.'idexpress'.$suffix,    'services' => [['code' => 'STD', 'name' => 'ID Express Standard', 'type' => 'regular']]],
                ['id' => 'anteraja',      'name' => 'Anteraja',          'code' => 'anteraja',      'logo_url' => $base.'anteraja'.$suffix,     'services' => [['code' => 'ND', 'name' => 'Anteraja Next Day', 'type' => 'express'], ['code' => '1D', 'name' => 'Anteraja 1 Day', 'type' => 'express']]],
                ['id' => 'grab',          'name' => 'Grab',              'code' => 'grab',          'logo_url' => $base.'grab'.$suffix,         'services' => [['code' => 'instant', 'name' => 'Grab Instant', 'type' => 'instant'], ['code' => 'same_day', 'name' => 'Grab Same Day', 'type' => 'same_day']]],
                ['id' => 'gojek',         'name' => 'GoSend',            'code' => 'gojek',         'logo_url' => $base.'gojek'.$suffix,        'services' => [['code' => 'instant', 'name' => 'GoSend Instant', 'type' => 'instant'], ['code' => 'same_day', 'name' => 'GoSend Same Day', 'type' => 'same_day']]],
                ['id' => 'sap',           'name' => 'SAP Express',       'code' => 'sap',           'logo_url' => $base.'sap'.$suffix,          'services' => [['code' => 'SDS', 'name' => 'SAP Regular', 'type' => 'regular']]],
                ['id' => 'jdl',           'name' => 'JDL Express',       'code' => 'jdl',           'logo_url' => $base.'jdl'.$suffix,          'services' => [['code' => 'ECS', 'name' => 'JDL Express', 'type' => 'regular']]],
                ['id' => 'paxel',         'name' => 'Paxel',             'code' => 'paxel',         'logo_url' => $base.'paxel'.$suffix,        'services' => [['code' => 'sameday', 'name' => 'Paxel Same Day', 'type' => 'same_day']]],
                ['id' => 'deliveree',     'name' => 'Deliveree',         'code' => 'deliveree',     'logo_url' => $base.'deliveree'.$suffix,    'services' => [['code' => 'ltl', 'name' => 'Deliveree LTL', 'type' => 'trucking']]],
                ['id' => 'lion_parcel',   'name' => 'Lion Parcel',       'code' => 'lion_parcel',   'logo_url' => $base.'lion'.$suffix,         'services' => [['code' => 'REG', 'name' => 'Lion Regular', 'type' => 'regular'], ['code' => 'ONTE', 'name' => 'Lion One Day', 'type' => 'express']]],
                ['id' => 'rpx',           'name' => 'RPX',               'code' => 'rpx',           'logo_url' => $base.'rpx'.$suffix,          'services' => [['code' => 'RGP', 'name' => 'RPX Regular', 'type' => 'regular']]],
                ['id' => 'wahana',        'name' => 'Wahana',            'code' => 'wahana',        'logo_url' => $base.'wahana'.$suffix,       'services' => [['code' => 'WAH', 'name' => 'Wahana Regular', 'type' => 'regular']]],
                ['id' => 'pos_indonesia', 'name' => 'Pos Indonesia',     'code' => 'pos_indonesia', 'logo_url' => $base.'pos'.$suffix,          'services' => [['code' => 'Pos Reguler', 'name' => 'Pos Reguler', 'type' => 'regular']]],
                ['id' => 'lalamove',      'name' => 'Lalamove',          'code' => 'lalamove',      'logo_url' => $base.'lalamove'.$suffix,     'services' => [['code' => 'MOTORCYCLE', 'name' => 'Lalamove Motor', 'type' => 'instant']]],
                ['id' => 'rara',          'name' => 'RARA Delivery',     'code' => 'rara',          'logo_url' => $base.'rara'.$suffix,         'services' => [['code' => 'same_day', 'name' => 'RARA Same Day', 'type' => 'same_day']]],
                ['id' => 'dhl',           'name' => 'DHL',               'code' => 'dhl',           'logo_url' => $base.'dhl'.$suffix,          'services' => [['code' => 'PDO', 'name' => 'DHL Domestic', 'type' => 'regular']]],
                ['id' => 'tlx',           'name' => 'TLX',               'code' => 'tlx',           'logo_url' => $base.'tlx'.$suffix,          'services' => [['code' => 'STD', 'name' => 'TLX Standard', 'type' => 'regular']]],
                ['id' => 'fedex',         'name' => 'FedEx',             'code' => 'fedex',         'logo_url' => $base.'fedex'.$suffix,        'services' => [['code' => 'IP', 'name' => 'FedEx International Priority', 'type' => 'express']]],
                ['id' => 'jet',           'name' => 'JET Express',       'code' => 'jet',           'logo_url' => $base.'jet'.$suffix,          'services' => [['code' => 'REG', 'name' => 'JET Regular', 'type' => 'regular']]],
                ['id' => 'alfatrex',      'name' => 'Alfatrex',          'code' => 'alfatrex',      'logo_url' => $base.'alfatrex'.$suffix,     'services' => [['code' => 'STD', 'name' => 'Alfatrex Standard', 'type' => 'regular']]],
                ['id' => 'borzo',         'name' => 'Borzo',             'code' => 'borzo',         'logo_url' => $base.'borzo'.$suffix,        'services' => [['code' => 'instant', 'name' => 'Borzo Instant', 'type' => 'instant']]],
                ['id' => 'mrspeedy',      'name' => 'Borzo (mrSpeedy)',   'code' => 'mrspeedy',      'logo_url' => $base.'mrspeedy'.$suffix,     'services' => [['code' => 'instant', 'name' => 'mrSpeedy Instant', 'type' => 'instant']]],
                ['id' => 'sentral_cargo', 'name' => 'Sentral Cargo',     'code' => 'sentral_cargo', 'logo_url' => $base.'sentralcargo'.$suffix, 'services' => [['code' => 'REG', 'name' => 'Sentral Cargo Regular', 'type' => 'regular']]],
                ['id' => 'insan_kargo',   'name' => 'Insan Kargo',       'code' => 'insan_kargo',   'logo_url' => $base.'insankargo'.$suffix,   'services' => [['code' => 'REG', 'name' => 'Insan Kargo Regular', 'type' => 'regular']]],
            ],
        ];
    }

    private function mockAreasResponse(array $query): array
    {
        $input = $query['input'] ?? '';

        return [
            'success' => true,
            'areas' => $input !== '' ? [
                [
                    'id' => 'IDNP6IDNC148MOCK',
                    'name' => $input.' (Mock)',
                    'description' => "{$input} (Mock), DKI Jakarta",
                    'country' => 'ID',
                    'province' => 'DKI Jakarta',
                    'city' => $input,
                    'type' => $query['type'] ?? 'single',
                    'postal_codes' => ['12000'],
                ],
            ] : [],
        ];
    }

    private function validateRatesInput(array $data): void
    {
        $hasOrigin = ! empty($data['origin_area_id']) || ! empty($data['origin_postal_code']);
        $hasDestination = ! empty($data['destination_area_id']) || ! empty($data['destination_postal_code']);

        if (! $hasOrigin) {
            throw new ValidationException('Origin area_id or postal_code is required', [
                'origin' => ['Area ID or postal code is required'],
            ]);
        }

        if (! $hasDestination) {
            throw new ValidationException('Destination area_id or postal_code is required', [
                'destination' => ['Area ID or postal code is required'],
            ]);
        }

        if (empty($data['items']) || ! is_array($data['items'])) {
            throw new ValidationException('Items are required', [
                'items' => ['At least one item is required'],
            ]);
        }
    }

    private function validateOrderInput(array $data): void
    {
        $required = [
            'origin_contact_name' => 'Origin contact name is required',
            'origin_contact_phone' => 'Origin contact phone is required',
            'origin_address' => 'Origin address is required',
            'destination_contact_name' => 'Destination contact name is required',
            'destination_contact_phone' => 'Destination contact phone is required',
            'destination_address' => 'Destination address is required',
            'courier_company' => 'Courier company is required',
            'courier_type' => 'Courier type is required',
        ];

        $errors = [];

        foreach ($required as $field => $message) {
            if (empty($data[$field])) {
                $errors[$field] = [$message];
            }
        }

        if (empty($data['items']) || ! is_array($data['items'])) {
            $errors['items'] = ['At least one item is required'];
        }

        if (! empty($errors)) {
            throw new ValidationException('Validation failed', $errors);
        }
    }

    private function calculateTotalWeight(array $items): int
    {
        return array_sum(array_map(
            fn (array $item) => ($item['weight'] ?? 0) * ($item['quantity'] ?? 1),
            $items
        ));
    }

    private function calculateMockPrice(array $items): int
    {
        $totalWeight = $this->calculateTotalWeight($items);
        $basePrice = 15000;
        $weightFactor = ceil($totalWeight / 1000) * 3000;

        return $basePrice + $weightFactor + rand(-1000, 1000);
    }

    private function extractOrderIdFromUri(string $uri): ?string
    {
        // Extract order ID from /v1/orders/{orderId} or /v1/orders/{orderId}/cancel
        // Case-insensitive to handle any casing in order IDs
        if (preg_match('/orders\/([A-Za-z0-9\-]+)/i', $uri, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function checkErrorSimulation(): void
    {
        $errors = config('biteship.mock_mode.errors', []);

        if (! empty($errors['authentication'])) {
            throw new AuthenticationException('Mock authentication error');
        }

        if (! empty($errors['rate_limit'])) {
            throw new RateLimitException('Mock rate limit exceeded.', 60);
        }

        if (! empty($errors['validation'])) {
            throw new ValidationException('Mock validation error', [
                'mock' => ['Simulated validation error'],
            ]);
        }

        if (! empty($errors['server'])) {
            throw new ApiException('Mock server error', 500);
        }
    }

    private function simulateDelay(): void
    {
        $delay = (int) config('biteship.mock_mode.delay', 0);

        if ($delay > 0) {
            usleep($delay * 1000); // Convert ms to microseconds
        }
    }
}
