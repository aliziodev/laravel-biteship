<?php

namespace Aliziodev\Biteship\DTOs\Tracking;

use Carbon\Carbon;

class TrackingHistory
{
    public function __construct(
        public readonly string $note,
        public readonly string $service_type,
        public readonly string $status,
        public readonly Carbon $updated_at,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            note: $data['note'] ?? '',
            service_type: $data['service_type'] ?? '',
            status: $data['status'] ?? '',
            updated_at: Carbon::parse($data['updated_at']),
        );
    }
}
