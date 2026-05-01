<?php

namespace Aliziodev\Biteship\Services;

use Aliziodev\Biteship\Contracts\BiteshipClientInterface;
use Aliziodev\Biteship\DTOs\Rate\RateRequest;
use Aliziodev\Biteship\DTOs\Rate\RateResponse;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class RateService
{
    private bool $shouldCache;

    public function __construct(
        private readonly BiteshipClientInterface $client,
        private readonly ?CacheRepository $cache = null,
    ) {
        $this->shouldCache = (bool) config('biteship.cache.enabled', true);
    }

    /**
     * Cek ongkir. Hasil di-cache sesuai config TTL.
     */
    public function check(RateRequest $request): RateResponse
    {
        if ($this->shouldCache && $this->cache !== null) {
            $key = $this->cacheKey($request);
            $ttl = (int) config('biteship.cache.ttl', 900);

            $data = $this->cache->remember($key, $ttl, function () use ($request) {
                return $this->client->post('/v1/rates/couriers', $request->toArray());
            });
        } else {
            $data = $this->client->post('/v1/rates/couriers', $request->toArray());
        }

        return RateResponse::fromArray($data);
    }

    /**
     * Bypass cache — selalu hit API.
     */
    public function fresh(): static
    {
        $clone = clone $this;
        $clone->shouldCache = false;

        return $clone;
    }

    /**
     * Invalidate cache untuk payload tertentu.
     */
    public function forget(RateRequest $request): bool
    {
        if ($this->cache === null) {
            return false;
        }

        return $this->cache->forget($this->cacheKey($request));
    }

    private function cacheKey(RateRequest $request): string
    {
        $payload = $request->toArray();

        // Normalize item order agar payload yang sama tapi urutan berbeda tetap sama key-nya
        if (isset($payload['items']) && is_array($payload['items'])) {
            usort($payload['items'], fn ($a, $b) => ($a['name'] ?? '') <=> ($b['name'] ?? ''));
        }

        $prefix = config('biteship.cache.prefix', 'biteship');

        return $prefix.':rates:'.sha1(json_encode($payload));
    }
}
