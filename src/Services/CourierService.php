<?php

namespace Aliziodev\Biteship\Services;

use Aliziodev\Biteship\Contracts\BiteshipClientInterface;
use Illuminate\Support\Collection;

class CourierService
{
    public function __construct(
        private readonly BiteshipClientInterface $client,
    ) {}

    /**
     * List semua kurir yang tersedia di akun Biteship.
     *
     * @return Collection<int, array>
     */
    public function all(): Collection
    {
        $data = $this->client->get('/v1/couriers');

        return collect($data['couriers'] ?? []);
    }
}
