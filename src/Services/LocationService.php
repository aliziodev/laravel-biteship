<?php

namespace Aliziodev\Biteship\Services;

use Aliziodev\Biteship\Contracts\BiteshipClientInterface;
use Illuminate\Support\Collection;

class LocationService
{
    public function __construct(
        private readonly BiteshipClientInterface $client,
    ) {}

    /**
     * Search area berdasarkan input teks (autocomplete).
     * Cocok untuk form input alamat pengiriman.
     *
     * @return Collection<int, array>
     */
    public function search(string $input, string $type = 'single'): Collection
    {
        $data = $this->client->get('/v1/maps/areas', [
            'countries' => 'ID',
            'input' => $input,
            'type' => $type,
        ]);

        return collect($data['areas'] ?? []);
    }
}
