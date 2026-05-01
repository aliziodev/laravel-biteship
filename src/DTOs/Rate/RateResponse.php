<?php

namespace Aliziodev\Biteship\DTOs\Rate;

use Illuminate\Support\Collection;

class RateResponse
{
    /** @param Collection<int, CourierRate> $pricing */
    public function __construct(
        public readonly bool $success,
        public readonly string $message,
        public readonly Collection $pricing,
    ) {}

    public static function fromArray(array $data): static
    {
        $pricing = collect($data['pricing'] ?? [])
            ->map(fn (array $item) => CourierRate::fromArray($item));

        return new static(
            success: $data['success'] ?? false,
            message: $data['message'] ?? '',
            pricing: $pricing,
        );
    }

    /** Cheapest rate dari semua kurir. */
    public function cheapest(): ?CourierRate
    {
        return $this->pricing->sortBy('price')->first();
    }

    /** Filter berdasarkan kode kurir, misal 'jne', 'sicepat'. */
    public function byCourier(string $courierCode): Collection
    {
        return $this->pricing->filter(
            fn (CourierRate $r) => $r->courierCode === $courierCode
        )->values();
    }

    /** Filter hanya kurir yang support COD. */
    public function codAvailable(): Collection
    {
        return $this->pricing->filter(
            fn (CourierRate $r) => $r->codAvailable
        )->values();
    }

    public function isEmpty(): bool
    {
        return $this->pricing->isEmpty();
    }
}
