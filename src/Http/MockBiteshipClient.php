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
        // Struktur flat per service — sama persis dengan real API Biteship /v1/couriers.
        // Satu kurir bisa punya banyak entry (satu per layanan).
        return [
            'success' => true,
            'couriers' => [
                // Gojek
                ['available_collection_method' => ['pickup'], 'available_for_cash_on_delivery' => false, 'available_for_proof_of_delivery' => false, 'available_for_instant_waybill_id' => true, 'courier_name' => 'Gojek', 'courier_code' => 'gojek', 'courier_service_name' => 'Instant', 'courier_service_code' => 'instant', 'tier' => 'premium', 'description' => 'On Demand Instant (bike)', 'service_type' => 'same_day', 'shipping_type' => 'parcel', 'shipment_duration_range' => '1 - 3', 'shipment_duration_unit' => 'hours'],
                ['available_collection_method' => ['pickup'], 'available_for_cash_on_delivery' => false, 'available_for_proof_of_delivery' => false, 'available_for_instant_waybill_id' => true, 'courier_name' => 'Gojek', 'courier_code' => 'gojek', 'courier_service_name' => 'Same Day', 'courier_service_code' => 'same_day', 'tier' => 'premium', 'description' => 'On Demand within 8 hours (bike)', 'service_type' => 'same_day', 'shipping_type' => 'parcel', 'shipment_duration_range' => '6 - 8', 'shipment_duration_unit' => 'hours'],
                // Grab
                ['available_collection_method' => ['pickup'], 'available_for_cash_on_delivery' => false, 'available_for_proof_of_delivery' => false, 'available_for_instant_waybill_id' => true, 'courier_name' => 'Grab', 'courier_code' => 'grab', 'courier_service_name' => 'Instant', 'courier_service_code' => 'instant', 'tier' => 'premium', 'description' => 'On Demand Instant (bike)', 'service_type' => 'same_day', 'shipping_type' => 'parcel', 'shipment_duration_range' => '1 - 3', 'shipment_duration_unit' => 'hours'],
                ['available_collection_method' => ['pickup'], 'available_for_cash_on_delivery' => false, 'available_for_proof_of_delivery' => false, 'available_for_instant_waybill_id' => true, 'courier_name' => 'Grab', 'courier_code' => 'grab', 'courier_service_name' => 'Same Day', 'courier_service_code' => 'same_day', 'tier' => 'premium', 'description' => 'On Demand within 8 hours (bike)', 'service_type' => 'same_day', 'shipping_type' => 'parcel', 'shipment_duration_range' => '6 - 8', 'shipment_duration_unit' => 'hours'],
                // JNE
                ['available_collection_method' => ['pickup'], 'available_for_cash_on_delivery' => false, 'available_for_proof_of_delivery' => false, 'available_for_instant_waybill_id' => true, 'courier_name' => 'JNE', 'courier_code' => 'jne', 'courier_service_name' => 'Reguler', 'courier_service_code' => 'reg', 'tier' => 'free', 'description' => 'Regular service', 'service_type' => 'standard', 'shipping_type' => 'parcel', 'shipment_duration_range' => '2 - 3', 'shipment_duration_unit' => 'days'],
                ['available_collection_method' => ['pickup'], 'available_for_cash_on_delivery' => false, 'available_for_proof_of_delivery' => false, 'available_for_instant_waybill_id' => true, 'courier_name' => 'JNE', 'courier_code' => 'jne', 'courier_service_name' => 'YES', 'courier_service_code' => 'yes', 'tier' => 'essentials', 'description' => 'Express, next day', 'service_type' => 'overnight', 'shipping_type' => 'parcel', 'shipment_duration_range' => '1', 'shipment_duration_unit' => 'days'],
                ['available_collection_method' => ['pickup'], 'available_for_cash_on_delivery' => false, 'available_for_proof_of_delivery' => false, 'available_for_instant_waybill_id' => true, 'courier_name' => 'JNE', 'courier_code' => 'jne', 'courier_service_name' => 'OKE', 'courier_service_code' => 'oke', 'tier' => 'free', 'description' => 'Economy service', 'service_type' => 'standard', 'shipping_type' => 'parcel', 'shipment_duration_range' => '3 - 4', 'shipment_duration_unit' => 'days'],
                // TIKI
                ['available_collection_method' => ['pickup'], 'available_for_cash_on_delivery' => true, 'available_for_proof_of_delivery' => false, 'available_for_instant_waybill_id' => true, 'courier_name' => 'TIKI', 'courier_code' => 'tiki', 'courier_service_name' => 'REG', 'courier_service_code' => 'reg', 'tier' => 'free', 'description' => 'Layanan reguler', 'service_type' => 'standard', 'shipping_type' => 'parcel', 'shipment_duration_range' => '2 - 3', 'shipment_duration_unit' => 'days'],
                ['available_collection_method' => ['pickup'], 'available_for_cash_on_delivery' => true, 'available_for_proof_of_delivery' => false, 'available_for_instant_waybill_id' => true, 'courier_name' => 'TIKI', 'courier_code' => 'tiki', 'courier_service_name' => 'ONS', 'courier_service_code' => 'ons', 'tier' => 'essentials', 'description' => 'One night service', 'service_type' => 'overnight', 'shipping_type' => 'parcel', 'shipment_duration_range' => '1', 'shipment_duration_unit' => 'days'],
                // Ninja
                ['available_collection_method' => ['pickup'], 'available_for_cash_on_delivery' => false, 'available_for_proof_of_delivery' => false, 'available_for_instant_waybill_id' => false, 'courier_name' => 'Ninja', 'courier_code' => 'ninja', 'courier_service_name' => 'Standard', 'courier_service_code' => 'standard', 'tier' => 'free', 'description' => 'Layanan standard', 'service_type' => 'standard', 'shipping_type' => 'parcel', 'shipment_duration_range' => '2 - 3', 'shipment_duration_unit' => 'days'],
                // Lion
                ['available_collection_method' => ['pickup', 'drop_off'], 'available_for_cash_on_delivery' => false, 'available_for_proof_of_delivery' => false, 'available_for_instant_waybill_id' => false, 'courier_name' => 'Lion', 'courier_code' => 'lion', 'courier_service_name' => 'Reg Pack', 'courier_service_code' => 'reg_pack', 'description' => 'Layanan standard', 'service_type' => 'standard', 'shipping_type' => 'parcel', 'shipment_duration_range' => '2 - 3', 'shipment_duration_unit' => 'days'],
                // SiCepat
                ['available_collection_method' => ['pickup'], 'available_for_cash_on_delivery' => false, 'available_for_proof_of_delivery' => false, 'available_for_instant_waybill_id' => true, 'courier_name' => 'SiCepat', 'courier_code' => 'sicepat', 'courier_service_name' => 'Reguler', 'courier_service_code' => 'reg', 'tier' => 'free', 'description' => 'Layanan reguler', 'service_type' => 'standard', 'shipping_type' => 'parcel', 'shipment_duration_range' => '2 - 3', 'shipment_duration_unit' => 'days'],
                ['available_collection_method' => ['pickup'], 'available_for_cash_on_delivery' => false, 'available_for_proof_of_delivery' => false, 'available_for_instant_waybill_id' => true, 'courier_name' => 'SiCepat', 'courier_code' => 'sicepat', 'courier_service_name' => 'Best', 'courier_service_code' => 'best', 'tier' => 'essentials', 'description' => 'Besok sampai tujuan', 'service_type' => 'overnight', 'shipping_type' => 'parcel', 'shipment_duration_range' => '1', 'shipment_duration_unit' => 'days'],
                ['available_collection_method' => ['pickup'], 'available_for_cash_on_delivery' => false, 'available_for_proof_of_delivery' => false, 'available_for_instant_waybill_id' => true, 'courier_name' => 'SiCepat', 'courier_code' => 'sicepat', 'courier_service_name' => 'SDS', 'courier_service_code' => 'sds', 'tier' => 'standard', 'description' => 'Same day service', 'service_type' => 'same_day', 'shipping_type' => 'parcel', 'shipment_duration_range' => '1', 'shipment_duration_unit' => 'days'],
                // Sentral Cargo
                ['available_collection_method' => ['pickup'], 'available_for_cash_on_delivery' => false, 'available_for_proof_of_delivery' => false, 'available_for_instant_waybill_id' => true, 'courier_name' => 'Sentral Cargo', 'courier_code' => 'sentralcargo', 'courier_service_name' => 'Land Electronic', 'courier_service_code' => 'land_electronic', 'tier' => 'free', 'description' => 'Layanan Elektronik via Darat', 'service_type' => 'standard', 'shipping_type' => 'freight', 'shipment_duration_range' => '3 - 4', 'shipment_duration_unit' => 'days'],
                // J&T
                ['available_collection_method' => ['pickup'], 'available_for_cash_on_delivery' => false, 'available_for_proof_of_delivery' => false, 'available_for_instant_waybill_id' => true, 'courier_name' => 'J&T', 'courier_code' => 'jnt', 'courier_service_name' => 'EZ', 'courier_service_code' => 'ez', 'tier' => 'free', 'description' => 'Layanan reguler', 'service_type' => 'standard', 'shipping_type' => 'parcel', 'shipment_duration_range' => '2 - 3', 'shipment_duration_unit' => 'days'],
                // IDexpress
                ['available_collection_method' => ['pickup'], 'available_for_cash_on_delivery' => false, 'available_for_proof_of_delivery' => false, 'available_for_instant_waybill_id' => true, 'courier_name' => 'IDexpress', 'courier_code' => 'idexpress', 'courier_service_name' => 'Reguler', 'courier_service_code' => 'reg', 'tier' => 'free', 'description' => 'Layanan reguler', 'service_type' => 'standard', 'shipping_type' => 'parcel', 'shipment_duration_range' => '2 - 3', 'shipment_duration_unit' => 'days'],
                // RPX
                ['available_collection_method' => ['pickup'], 'available_for_cash_on_delivery' => false, 'available_for_proof_of_delivery' => false, 'available_for_instant_waybill_id' => false, 'courier_name' => 'RPX', 'courier_code' => 'rpx', 'courier_service_name' => 'Reguler Package', 'courier_service_code' => 'rgp', 'tier' => 'free', 'description' => 'Pengiriman standard', 'service_type' => 'standard', 'shipping_type' => 'parcel', 'shipment_duration_range' => '2 - 4', 'shipment_duration_unit' => 'days'],
                // Wahana
                ['available_collection_method' => ['drop_off'], 'available_for_cash_on_delivery' => false, 'available_for_proof_of_delivery' => false, 'available_for_instant_waybill_id' => false, 'courier_name' => 'Wahana', 'courier_code' => 'wahana', 'courier_service_name' => 'Deno', 'courier_service_code' => 'deno', 'tier' => 'free', 'description' => 'Layanan standard', 'service_type' => 'standard', 'shipping_type' => 'parcel', 'shipment_duration_range' => '2 - 3', 'shipment_duration_unit' => 'days'],
                // Pos Indonesia
                ['available_collection_method' => ['drop_off', 'pickup'], 'available_for_cash_on_delivery' => false, 'available_for_proof_of_delivery' => false, 'available_for_instant_waybill_id' => false, 'courier_name' => 'Pos Indonesia', 'courier_code' => 'pos', 'courier_service_name' => 'Pos Reguler', 'courier_service_code' => 'reg', 'tier' => 'free', 'description' => 'Layanan reguler', 'service_type' => 'standard', 'shipping_type' => 'parcel', 'shipment_duration_range' => '1 - 3', 'shipment_duration_unit' => 'days'],
                // Anteraja
                ['available_collection_method' => ['pickup'], 'available_for_cash_on_delivery' => false, 'available_for_proof_of_delivery' => false, 'available_for_instant_waybill_id' => true, 'courier_name' => 'Anteraja', 'courier_code' => 'anteraja', 'courier_service_name' => 'Reguler', 'courier_service_code' => 'reg', 'tier' => 'free', 'description' => 'Regular shipment', 'service_type' => 'standard', 'shipping_type' => 'parcel', 'shipment_duration_range' => '2 - 3', 'shipment_duration_unit' => 'days'],
                ['available_collection_method' => ['pickup'], 'available_for_cash_on_delivery' => false, 'available_for_proof_of_delivery' => false, 'available_for_instant_waybill_id' => true, 'courier_name' => 'Anteraja', 'courier_code' => 'anteraja', 'courier_service_name' => 'Next Day', 'courier_service_code' => 'next_day', 'tier' => 'essentials', 'description' => 'Next day service delivery', 'service_type' => 'overnight', 'shipping_type' => 'parcel', 'shipment_duration_range' => '1', 'shipment_duration_unit' => 'days'],
                // SAP
                ['available_collection_method' => ['pickup'], 'available_for_cash_on_delivery' => false, 'available_for_proof_of_delivery' => false, 'available_for_instant_waybill_id' => true, 'courier_name' => 'SAP', 'courier_code' => 'sap', 'courier_service_name' => 'REG', 'courier_service_code' => 'reg', 'tier' => 'free', 'description' => 'Regular service', 'service_type' => 'standard', 'shipping_type' => 'parcel', 'shipment_duration_range' => '3 - 4', 'shipment_duration_unit' => 'days'],
                // Paxel
                ['available_collection_method' => ['pickup'], 'available_for_cash_on_delivery' => false, 'available_for_proof_of_delivery' => false, 'available_for_instant_waybill_id' => true, 'courier_name' => 'Paxel', 'courier_code' => 'paxel', 'courier_service_name' => 'Small Package', 'courier_service_code' => 'small', 'tier' => 'standard', 'description' => 'Layanan paket small', 'service_type' => 'standard', 'shipping_type' => 'parcel', 'shipment_duration_range' => '8 - 12', 'shipment_duration_unit' => 'hours'],
                // Borzo
                ['available_collection_method' => ['pickup'], 'available_for_cash_on_delivery' => false, 'available_for_proof_of_delivery' => false, 'available_for_instant_waybill_id' => true, 'courier_name' => 'Borzo', 'courier_code' => 'borzo', 'courier_service_name' => 'Instant Bike', 'courier_service_code' => 'instant_bike', 'tier' => 'standard', 'description' => 'Delivery using bike', 'service_type' => 'instant', 'shipping_type' => 'parcel', 'shipment_duration_range' => '1 - 3', 'shipment_duration_unit' => 'hours'],
                // Lalamove
                ['available_collection_method' => ['pickup'], 'available_for_cash_on_delivery' => false, 'available_for_proof_of_delivery' => false, 'available_for_instant_waybill_id' => true, 'courier_name' => 'Lalamove', 'courier_code' => 'lalamove', 'courier_service_name' => 'Motorcycle', 'courier_service_code' => 'motorcycle', 'tier' => 'premium', 'description' => 'Delivery using bike', 'service_type' => 'same_day', 'shipping_type' => 'parcel', 'shipment_duration_range' => '1 - 3', 'shipment_duration_unit' => 'hours'],
                // Dash Express
                ['available_collection_method' => ['pickup'], 'available_for_cash_on_delivery' => false, 'available_for_proof_of_delivery' => false, 'available_for_instant_waybill_id' => true, 'courier_name' => 'Dash Express', 'courier_code' => 'dash_express', 'courier_service_name' => 'Same Day', 'courier_service_code' => 'SAME_DAY', 'tier' => 'free', 'description' => 'Dash Same Day', 'service_type' => 'same_day', 'shipping_type' => 'parcel', 'shipment_duration_range' => '6 - 8', 'shipment_duration_unit' => 'hours'],
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
