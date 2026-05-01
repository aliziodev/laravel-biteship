<?php

namespace Aliziodev\Biteship\DTOs\Tracking;

use Aliziodev\Biteship\Enums\TrackingStatus;
use Illuminate\Support\Collection;

class TrackingResponse
{
    /** @param Collection<int, TrackingHistory> $history */
    public function __construct(
        public readonly string $order_id,
        public readonly ?string $waybill_id,
        public readonly string $courier_company,
        public readonly string $courier_type,
        public readonly TrackingStatus $status,
        public readonly Collection $history,
    ) {}

    public static function fromArray(array $data): static
    {
        $history = collect($data['history'] ?? [])
            ->map(fn (array $h) => TrackingHistory::fromArray($h));

        return new static(
            order_id: $data['id'] ?? '',
            waybill_id: $data['courier']['waybill_id'] ?? null,
            courier_company: $data['courier']['company'] ?? '',
            courier_type: $data['courier']['type'] ?? '',
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
