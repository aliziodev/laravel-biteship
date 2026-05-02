<?php

namespace Aliziodev\Biteship\Services;

use Aliziodev\Biteship\Contracts\BiteshipClientInterface;

class LocationService
{
    public function __construct(
        private readonly BiteshipClientInterface $client,
    ) {}

    /**
     * Membuat lokasi tersimpan baru di Biteship.
     *
     * @param array{
     *   name: string,
     *   contact_name: string,
     *   contact_phone: string,
     *   address: string,
     *   postal_code: string|int,
     *   latitude: float|string,
     *   longitude: float|string,
     *   type: string,
     *   note?: string
     * } $payload
     */
    public function create(array $payload): array
    {
        return $this->client->post('/v1/locations', $payload);
    }

    /**
     * Mengambil data lokasi tersimpan berdasarkan ID.
     */
    public function find(string $id): array
    {
        return $this->client->get("/v1/locations/{$id}");
    }

    /**
     * Memperbarui data lokasi tersimpan.
     * Catatan: Biteship menggunakan POST untuk update /v1/locations/:id.
     * Hanya sertakan field yang ingin diubah pada $payload.
     */
    public function update(string $id, array $payload): array
    {
        return $this->client->post("/v1/locations/{$id}", $payload);
    }

    /**
     * Menghapus lokasi tersimpan.
     */
    public function delete(string $id): array
    {
        return $this->client->delete("/v1/locations/{$id}");
    }
}
