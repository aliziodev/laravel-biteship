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
    private array $mockOrders = [];

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
        $totalWeight = $this->calculateTotalWeight($data['items'] ?? []);
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

        // Store mock order for retrieval
        $this->mockOrders[$orderId] = $response;
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

        // Return not found but with mock data if not in cache
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
        if (preg_match('/orders\/([A-Z0-9\-]+)/', $uri, $matches)) {
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
