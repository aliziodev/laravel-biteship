<?php

namespace Aliziodev\Biteship\DTOs\Label;

class Label
{
    public function __construct(
        public readonly string $courierName,
        public readonly string $courierType,
        public readonly ?string $waybillId,
        public readonly ?string $trackingId,
        public readonly string $senderName,
        public readonly string $senderPhone,
        public readonly string $senderAddress,
        public readonly string $recipientName,
        public readonly string $recipientPhone,
        public readonly string $recipientAddress,
        public readonly int $totalWeight,       // gram
        public readonly int $codAmount,         // 0 jika bukan COD
        public readonly array $items,
    ) {}

    public static function fromOrderResponse(array $raw): static
    {
        $totalWeight = array_sum(array_map(
            fn (array $item) => ($item['weight'] ?? 0) * ($item['quantity'] ?? 1),
            $raw['items'] ?? [],
        ));

        // Prefer shipper info for label display (branding), fallback ke origin
        // Shipper bisa berbeda dari origin — misal toko pakai nama brand,
        // sementara origin adalah kontak gudang/warehouse.
        // Address tetap dari origin karena shipper tidak punya address di Biteship API.
        return new static(
            courierName: $raw['courier']['company'] ?? '',
            courierType: $raw['courier']['type'] ?? '',
            waybillId: $raw['courier']['waybill_id'] ?? null,
            trackingId: $raw['courier']['tracking_id'] ?? null,
            senderName: $raw['shipper']['contact_name'] ?? $raw['origin']['contact_name'] ?? '',
            senderPhone: $raw['shipper']['contact_phone'] ?? $raw['origin']['contact_phone'] ?? '',
            senderAddress: $raw['origin']['address'] ?? '',
            recipientName: $raw['destination']['contact_name'] ?? '',
            recipientPhone: $raw['destination']['contact_phone'] ?? '',
            recipientAddress: $raw['destination']['address'] ?? '',
            totalWeight: $totalWeight,
            codAmount: $raw['destination']['cash_on_delivery'] ?? 0,
            items: $raw['items'] ?? [],
        );
    }

    public function isCod(): bool
    {
        return $this->codAmount > 0;
    }
}
