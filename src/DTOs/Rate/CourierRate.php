<?php

namespace Aliziodev\Biteship\DTOs\Rate;

class CourierRate
{
    public function __construct(
        public readonly string $courierName,
        public readonly string $courierCode,
        public readonly string $courierServiceName,
        public readonly string $courierServiceCode,
        public readonly string $type,           // 'regular', 'express', 'trucking', 'instant'
        public readonly string $description,
        public readonly int $duration,        // estimasi hari
        public readonly string $shipmentDuration,
        public readonly int $price,           // ongkir dalam rupiah
        public readonly ?int $insuranceRate,
        public readonly bool $codAvailable,
        public readonly ?float $codFeePercent,
        public readonly ?int $codFeeFlat,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            courierName: $data['courier_name'] ?? '',
            courierCode: $data['courier_code'] ?? '',
            courierServiceName: $data['courier_service_name'] ?? '',
            courierServiceCode: $data['courier_service_code'] ?? '',
            type: $data['type'] ?? '',
            description: $data['description'] ?? '',
            duration: $data['duration'] ?? 0,
            shipmentDuration: $data['shipment_duration_range'] ?? '',
            price: $data['price'] ?? 0,
            insuranceRate: $data['insurance_rate'] ?? null,
            codAvailable: $data['available_for_cash_on_delivery'] ?? false,
            codFeePercent: $data['available_for_cash_on_delivery_fee_percent'] ?? null,
            codFeeFlat: $data['available_for_cash_on_delivery_fee_flat'] ?? null,
        );
    }
}
