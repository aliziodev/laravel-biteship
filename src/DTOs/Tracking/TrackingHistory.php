<?php

namespace Aliziodev\Biteship\DTOs\Tracking;

use Carbon\Carbon;

class TrackingHistory
{
    public function __construct(
        public readonly string $note,
        public readonly string $serviceType,
        public readonly string $status,
        public readonly Carbon $updatedAt,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            note: $data['note'] ?? '',
            serviceType: $data['service_type'] ?? '',
            status: $data['status'] ?? '',
            updatedAt: Carbon::parse($data['updated_at']),
        );
    }
}
