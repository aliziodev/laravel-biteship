<?php

namespace Aliziodev\Biteship\DTOs\Rate;

class CourierRate
{
    public function __construct(
        public readonly string $courier_name,
        public readonly string $courier_code,
        public readonly string $courier_service_name,
        public readonly string $courier_service_code,
        public readonly string $type,           // 'regular', 'express', 'trucking', 'instant'
        public readonly string $description,
        public readonly string $duration,     // estimasi hari (contoh: "6 - 7 days")
        public readonly string $shipment_duration,
        public readonly int $price,           // ongkir dalam rupiah
        public readonly bool $insurance_available,
        public readonly ?int $insurance_fee,
        public readonly bool $cod_available,
        public readonly ?int $cod_fee,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            courier_name: $data['courier_name'] ?? '',
            courier_code: $data['courier_code'] ?? '',
            courier_service_name: $data['courier_service_name'] ?? '',
            courier_service_code: $data['courier_service_code'] ?? '',
            type: $data['type'] ?? '',
            description: $data['description'] ?? '',
            duration: $data['duration'] ?? '',
            shipment_duration: $data['shipment_duration_range'] ?? '',
            price: (int) ($data['price'] ?? 0),
            insurance_available: (bool) ($data['available_for_insurance'] ?? false),
            insurance_fee: isset($data['insurance_fee']) ? (int) $data['insurance_fee'] : null,
            cod_available: (bool) ($data['available_for_cash_on_delivery'] ?? false),
            cod_fee: isset($data['cash_on_delivery_fee']) ? (int) $data['cash_on_delivery_fee'] : null,
        );
    }
}
