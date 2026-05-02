<?php

namespace Aliziodev\Biteship\DTOs\Rate;

class RateRequest
{
    private array $origin = [];

    private array $destination = [];

    private array $items = [];

    private string $couriers = 'biteship'; // 'biteship' = semua kurir aktif

    private ?string $deliveryType = null;

    private ?string $deliveryDate = null;

    private ?string $deliveryTime = null;

    private ?string $type = null;

    private ?int $codAmount = null;

    private ?string $codType = null;

    private ?int $courierInsurance = null;

    private bool $forOrder = false;

    // --- Origin ---

    public function originAreaId(string $areaId): static
    {
        $this->origin['area_id'] = $areaId;

        return $this;
    }

    public function originPostalCode(string $postalCode): static
    {
        $this->origin['postal_code'] = $postalCode;

        return $this;
    }

    public function originCoordinate(float $lat, float $lng): static
    {
        $this->origin['coordinate'] = ['latitude' => $lat, 'longitude' => $lng];

        return $this;
    }

    public function originContact(string $name, string $phone): static
    {
        $this->origin['contact_name'] = $name;
        $this->origin['contact_phone'] = $phone;

        return $this;
    }

    public function originAddress(string $address, ?string $note = null): static
    {
        $this->origin['address'] = $address;

        if ($note !== null) {
            $this->origin['note'] = $note;
        }

        return $this;
    }

    /**
     * Auto-fill origin data dari config default_origin.
     */
    public function defaultOrigin(): static
    {
        $config = config('biteship.default_origin', []);

        if (! empty($config['area_id'])) {
            $this->origin['area_id'] = $config['area_id'];
        }

        if (! empty($config['postal_code'])) {
            $this->origin['postal_code'] = $config['postal_code'];
        }

        if (! empty($config['contact_name'])) {
            $this->origin['contact_name'] = $config['contact_name'];
        }

        if (! empty($config['contact_phone'])) {
            $this->origin['contact_phone'] = $config['contact_phone'];
        }

        if (! empty($config['contact_email'])) {
            $this->origin['contact_email'] = $config['contact_email'];
        }

        if (! empty($config['address'])) {
            $this->origin['address'] = $config['address'];
        }

        if (! empty($config['note'])) {
            $this->origin['note'] = $config['note'];
        }

        return $this;
    }

    // --- Destination ---

    public function destinationAreaId(string $areaId): static
    {
        $this->destination['area_id'] = $areaId;

        return $this;
    }

    public function destinationPostalCode(string $postalCode): static
    {
        $this->destination['postal_code'] = $postalCode;

        return $this;
    }

    public function destinationCoordinate(float $lat, float $lng): static
    {
        $this->destination['coordinate'] = ['latitude' => $lat, 'longitude' => $lng];

        return $this;
    }

    public function destinationContact(string $name, string $phone): static
    {
        $this->destination['contact_name'] = $name;
        $this->destination['contact_phone'] = $phone;

        return $this;
    }

    public function destinationAddress(string $address, ?string $note = null): static
    {
        $this->destination['address'] = $address;

        if ($note !== null) {
            $this->destination['note'] = $note;
        }

        return $this;
    }

    // --- Items ---

    /**
     * @param  array{name: string, value: int, weight: int, quantity: int, length?: int, width?: int, height?: int, description?: string, category?: string, sku?: string}  $item
     */
    public function addItem(array $item): static
    {
        $this->items[] = $item;

        return $this;
    }

    /** @param list<array> $items */
    public function items(array $items): static
    {
        $this->items = $items;

        return $this;
    }

    // --- Options ---

    /** Filter berdasarkan tipe layanan (misal: 'instant', 'regular', 'cargo') */
    public function type(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    /** Menghitung fee COD pada rates */
    public function cashOnDelivery(int $amount, string $type = '7_days'): static
    {
        $this->codAmount = $amount;
        $this->codType = $type;

        return $this;
    }

    /** Menghitung fee asuransi pada rates */
    public function courierInsurance(int $amount): static
    {
        $this->courierInsurance = $amount;

        return $this;
    }

    /** Tandai bahwa rates ini untuk order (berguna untuk token/akurasi asuransi) */
    public function forOrder(bool $forOrder = true): static
    {
        $this->forOrder = $forOrder;

        return $this;
    }

    // --- Couriers ---

    /** Spesifik kurir: 'jne', 'sicepat', dll. Bisa array atau string CSV. */
    public function couriers(string|array $couriers): static
    {
        $this->couriers = is_array($couriers) ? implode(',', $couriers) : $couriers;

        return $this;
    }

    /**
     * Pakai filter kurir dari config default_courier.
     * Prioritas: 'filter' (CSV) → 'company' (single) → tidak diubah (semua kurir aktif).
     */
    public function defaultCourier(): static
    {
        $config = config('biteship.default_courier', []);

        if (! empty($config['filter'])) {
            $this->couriers = $config['filter'];
        } elseif (! empty($config['company'])) {
            $this->couriers = $config['company'];
        }

        return $this;
    }

    // --- Delivery Type ---

    public function deliverNow(): static
    {
        $this->deliveryType = 'now';

        return $this;
    }

    public function deliverScheduled(string $date, string $time): static
    {
        $this->deliveryType = 'scheduled';
        $this->deliveryDate = $date;
        $this->deliveryTime = $time;

        return $this;
    }

    /**
     * Detect which location method is used: area_id, postal_code, or coordinate.
     */
    private function getLocationMethod(array $location): ?string
    {
        if (isset($location['area_id'])) {
            return 'area_id';
        }

        if (isset($location['postal_code'])) {
            return 'postal_code';
        }

        if (isset($location['coordinate'])) {
            return 'coordinate';
        }

        return null;
    }

    // --- Build ---

    public function toArray(): array
    {

        $payload = [
            'origin_contact_name' => $this->origin['contact_name'] ?? '',
            'origin_contact_phone' => $this->origin['contact_phone'] ?? '',
            'origin_address' => $this->origin['address'] ?? '',
            'destination_contact_name' => $this->destination['contact_name'] ?? '',
            'destination_contact_phone' => $this->destination['contact_phone'] ?? '',
            'destination_address' => $this->destination['address'] ?? '',
            'couriers' => $this->couriers,
            'items' => $this->items,
        ];

        // Location — area_id / postal_code
        foreach (['area_id', 'postal_code'] as $field) {
            if (isset($this->origin[$field])) {
                $payload['origin_'.$field] = $this->origin[$field];
            }

            if (isset($this->destination[$field])) {
                $payload['destination_'.$field] = $this->destination[$field];
            }
        }

        // Coordinates
        if (isset($this->origin['coordinate'])) {
            $payload['origin_latitude'] = $this->origin['coordinate']['latitude'];
            $payload['origin_longitude'] = $this->origin['coordinate']['longitude'];
        }

        if (isset($this->destination['coordinate'])) {
            $payload['destination_latitude'] = $this->destination['coordinate']['latitude'];
            $payload['destination_longitude'] = $this->destination['coordinate']['longitude'];
        }

        // Optional fields
        if (isset($this->origin['note'])) {
            $payload['origin_note'] = $this->origin['note'];
        }

        if (isset($this->destination['note'])) {
            $payload['destination_note'] = $this->destination['note'];
        }

        if ($this->deliveryType !== null) {
            $payload['delivery_type'] = $this->deliveryType;
        }

        if ($this->deliveryDate !== null) {
            $payload['delivery_date'] = $this->deliveryDate;
            $payload['delivery_time'] = $this->deliveryTime;
        }

        if ($this->type !== null) {
            $payload['type'] = $this->type;
        }

        if ($this->codAmount !== null) {
            $payload['destination_cash_on_delivery'] = $this->codAmount;
            $payload['destination_cash_on_delivery_type'] = $this->codType;
        }

        if ($this->courierInsurance !== null) {
            $payload['courier_insurance'] = $this->courierInsurance;
        }

        if ($this->forOrder) {
            $payload['for_order'] = true;
        }

        return $payload;
    }
}
