<?php

namespace Aliziodev\Biteship\DTOs\Order;

class OrderRequest
{
    private array $origin = [];

    private array $destination = [];

    private array $items = [];

    private array $courier = [];

    private ?string $referenceId = null;

    private ?string $notes = null;

    private array $shipper = [];

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

    public function originContact(string $name, string $phone, ?string $email = null): static
    {
        $this->origin['contact_name'] = $name;
        $this->origin['contact_phone'] = $phone;

        if ($email !== null) {
            $this->origin['contact_email'] = $email;
        }

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

    public function destinationContact(string $name, string $phone, ?string $email = null): static
    {
        $this->destination['contact_name'] = $name;
        $this->destination['contact_phone'] = $phone;

        if ($email !== null) {
            $this->destination['contact_email'] = $email;
        }

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

    /** Cash on delivery — jumlah dalam rupiah. */
    public function cashOnDelivery(int $amount): static
    {
        $this->destination['cash_on_delivery'] = $amount;

        return $this;
    }

    // --- Courier ---

    public function courier(string $company, string $type, ?string $insurance = null): static
    {
        $this->courier['courier_company'] = $company;
        $this->courier['courier_type'] = $type;

        if ($insurance !== null) {
            $this->courier['courier_insurance'] = $insurance;
        }

        return $this;
    }

    /**
     * Auto-fill courier dari config default_courier.
     */
    public function defaultCourier(): static
    {
        $config = config('biteship.default_courier', []);

        if (! empty($config['company'])) {
            $this->courier['courier_company'] = $config['company'];
        }

        if (! empty($config['type'])) {
            $this->courier['courier_type'] = $config['type'];
        }

        if (! empty($config['insurance'])) {
            $this->courier['courier_insurance'] = $config['insurance'];
        }

        return $this;
    }

    // --- Shipper ---

    /**
     * Set shipper data untuk branding/labeling pengirim.
     * Shipper terpisah dari origin (lokasi pickup).
     */
    public function shipper(string $name, string $phone, ?string $email = null, ?string $organization = null): static
    {
        $this->shipper['contact_name'] = $name;
        $this->shipper['contact_phone'] = $phone;

        if ($email !== null) {
            $this->shipper['contact_email'] = $email;
        }

        if ($organization !== null) {
            $this->shipper['organization'] = $organization;
        }

        return $this;
    }

    public function shipperName(string $name): static
    {
        $this->shipper['contact_name'] = $name;

        return $this;
    }

    public function shipperPhone(string $phone): static
    {
        $this->shipper['contact_phone'] = $phone;

        return $this;
    }

    public function shipperEmail(string $email): static
    {
        $this->shipper['contact_email'] = $email;

        return $this;
    }

    public function shipperOrganization(string $organization): static
    {
        $this->shipper['organization'] = $organization;

        return $this;
    }

    /**
     * Auto-fill shipper data dari config default_shipper.
     */
    public function defaultShipper(): static
    {
        $config = config('biteship.default_shipper', []);

        if (! empty($config['contact_name'])) {
            $this->shipper['contact_name'] = $config['contact_name'];
        }

        if (! empty($config['contact_phone'])) {
            $this->shipper['contact_phone'] = $config['contact_phone'];
        }

        if (! empty($config['contact_email'])) {
            $this->shipper['contact_email'] = $config['contact_email'];
        }

        if (! empty($config['organization'])) {
            $this->shipper['organization'] = $config['organization'];
        }

        return $this;
    }

    // --- Items ---

    /** @param array{name: string, value: int, weight: int, quantity: int, length?: int, width?: int, height?: int, description?: string} $item */
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

    // --- Metadata ---

    public function referenceId(string $id): static
    {
        $this->referenceId = $id;

        return $this;
    }

    public function notes(string $notes): static
    {
        $this->notes = $notes;

        return $this;
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

            'courier_company' => $this->courier['courier_company'] ?? '',
            'courier_type' => $this->courier['courier_type'] ?? '',

            'items' => $this->items,
        ];

        // Location fields
        foreach (['area_id', 'postal_code'] as $field) {
            if (isset($this->origin[$field])) {
                $payload['origin_'.$field] = $this->origin[$field];
            }

            if (isset($this->destination[$field])) {
                $payload['destination_'.$field] = $this->destination[$field];
            }
        }

        // Optional origin
        if (isset($this->origin['contact_email'])) {
            $payload['origin_contact_email'] = $this->origin['contact_email'];
        }

        if (isset($this->origin['note'])) {
            $payload['origin_note'] = $this->origin['note'];
        }

        // Optional destination
        if (isset($this->destination['contact_email'])) {
            $payload['destination_contact_email'] = $this->destination['contact_email'];
        }

        if (isset($this->destination['note'])) {
            $payload['destination_note'] = $this->destination['note'];
        }

        if (isset($this->destination['cash_on_delivery'])) {
            $payload['destination_cash_on_delivery'] = $this->destination['cash_on_delivery'];
        }

        // Optional courier
        if (isset($this->courier['courier_insurance'])) {
            $payload['courier_insurance'] = $this->courier['courier_insurance'];
        }

        // Optional metadata
        if ($this->referenceId !== null) {
            $payload['reference_id'] = $this->referenceId;
        }

        if ($this->notes !== null) {
            $payload['notes'] = $this->notes;
        }

        // Optional shipper (branding/labeling)
        if (isset($this->shipper['contact_name'])) {
            $payload['shipper_contact_name'] = $this->shipper['contact_name'];
        }

        if (isset($this->shipper['contact_phone'])) {
            $payload['shipper_contact_phone'] = $this->shipper['contact_phone'];
        }

        if (isset($this->shipper['contact_email'])) {
            $payload['shipper_contact_email'] = $this->shipper['contact_email'];
        }

        if (isset($this->shipper['organization'])) {
            $payload['shipper_organization'] = $this->shipper['organization'];
        }

        return $payload;
    }
}
