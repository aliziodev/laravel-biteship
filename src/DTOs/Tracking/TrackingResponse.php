<?php

namespace Aliziodev\Biteship\DTOs\Tracking;

use Aliziodev\Biteship\Enums\TrackingStatus;
use Illuminate\Support\Collection;

class TrackingResponse
{
    /** @param Collection<int, TrackingHistory> $history */
    public function __construct(
        public readonly string $tracking_id,
        public readonly ?string $order_id,
        public readonly ?string $waybill_id,
        public readonly string $courier_company,
        public readonly ?string $driver_name,
        public readonly ?string $driver_phone,
        public readonly ?string $driver_photo_url,
        public readonly ?string $driver_plate_number,
        public readonly ?string $origin_contact_name,
        public readonly ?string $origin_address,
        public readonly ?string $destination_contact_name,
        public readonly ?string $destination_address,
        public readonly ?string $link,
        public readonly TrackingStatus $status,
        public readonly Collection $history,
    ) {}

    public static function fromArray(array $data): static
    {
        $history = collect($data['history'] ?? [])
            ->map(fn (array $h) => TrackingHistory::fromArray($h));

        return new static(
            tracking_id: $data['id'] ?? '',
            order_id: $data['order_id'] ?? null,
            waybill_id: $data['waybill_id'] ?? null,
            courier_company: $data['courier']['company'] ?? '',
            driver_name: $data['courier']['driver_name'] ?? null,
            driver_phone: $data['courier']['driver_phone'] ?? null,
            driver_photo_url: $data['courier']['driver_photo_url'] ?? null,
            driver_plate_number: $data['courier']['driver_plate_number'] ?? null,
            origin_contact_name: $data['origin']['contact_name'] ?? null,
            origin_address: $data['origin']['address'] ?? null,
            destination_contact_name: $data['destination']['contact_name'] ?? null,
            destination_address: $data['destination']['address'] ?? null,
            link: $data['link'] ?? null,
            status: TrackingStatus::tryFrom($data['status'] ?? '') ?? TrackingStatus::Confirmed,
            history: $history,
        );
    }

    public function latestNote(): string
    {
        return $this->history->last()?->note ?? '';
    }

    public function isFinal(): bool
    {
        return $this->status->isFinal();
    }
}
