<?php

namespace Aliziodev\Biteship\DTOs\Label;

class Label
{
    public function __construct(
        public readonly string $courier_name,
        public readonly string $courier_type,
        public readonly ?string $waybill_id,
        public readonly ?string $tracking_id,
        public readonly string $sender_name,
        public readonly string $sender_phone,
        public readonly string $sender_address,
        public readonly string $recipient_name,
        public readonly string $recipient_phone,
        public readonly string $recipient_address,
        public readonly int $total_weight,       // gram
        public readonly int $cod_amount,         // 0 jika bukan COD
        public readonly array $items,
    ) {}

    public static function fromOrderResponse(array $raw): static
    {
        $total_weight = array_sum(array_map(
            fn (array $item) => ($item['weight'] ?? 0) * ($item['quantity'] ?? 1),
            $raw['items'] ?? [],
        ));

        // Prefer shipper info for label display (branding), fallback ke origin
        // Shipper bisa berbeda dari origin — misal toko pakai nama brand,
        // sementara origin adalah kontak gudang/warehouse.
        // Address tetap dari origin karena shipper tidak punya address di Biteship API.
        return new static(
            courier_name: $raw['courier']['company'] ?? '',
            courier_type: $raw['courier']['type'] ?? '',
            waybill_id: $raw['courier']['waybill_id'] ?? null,
            tracking_id: $raw['courier']['tracking_id'] ?? null,
            sender_name: $raw['shipper']['contact_name'] ?? $raw['origin']['contact_name'] ?? '',
            sender_phone: $raw['shipper']['contact_phone'] ?? $raw['origin']['contact_phone'] ?? '',
            sender_address: $raw['origin']['address'] ?? '',
            recipient_name: $raw['destination']['contact_name'] ?? '',
            recipient_phone: $raw['destination']['contact_phone'] ?? '',
            recipient_address: $raw['destination']['address'] ?? '',
            total_weight: $total_weight,
            cod_amount: $raw['destination']['cash_on_delivery'] ?? 0,
            items: $raw['items'] ?? [],
        );
    }

    public function isCod(): bool
    {
        return $this->cod_amount > 0;
    }
}
