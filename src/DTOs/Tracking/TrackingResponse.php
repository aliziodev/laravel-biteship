<?php

namespace Aliziodev\Biteship\DTOs\Tracking;

use Aliziodev\Biteship\Enums\TrackingStatus;
use Illuminate\Support\Collection;

class TrackingResponse
{
    /** @param Collection<int, TrackingHistory> $history */
    public function __construct(
        public readonly string $orderId,
        public readonly ?string $waybillId,
        public readonly string $courierCompany,
        public readonly string $courierType,
        public readonly TrackingStatus $status,
        public readonly Collection $history,
    ) {}

    public static function fromArray(array $data): static
    {
        $history = collect($data['history'] ?? [])
            ->map(fn (array $h) => TrackingHistory::fromArray($h));

        return new static(
            orderId: $data['id'] ?? '',
            waybillId: $data['courier']['waybill_id'] ?? null,
            courierCompany: $data['courier']['company'] ?? '',
            courierType: $data['courier']['type'] ?? '',
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
