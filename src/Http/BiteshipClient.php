<?php

namespace Aliziodev\Biteship\Http;

use Aliziodev\Biteship\Contracts\BiteshipClientInterface;
use Aliziodev\Biteship\Exceptions\ApiException;
use Aliziodev\Biteship\Exceptions\AuthenticationException;
use Aliziodev\Biteship\Exceptions\RateLimitException;
use Aliziodev\Biteship\Exceptions\ValidationException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;

class BiteshipClient implements BiteshipClientInterface
{
    private readonly string $baseUrl;

    private readonly int $timeout;

    public function __construct(
        private readonly HttpFactory $http,
        private readonly string $apiKey,
    ) {
        $this->baseUrl = rtrim(config('biteship.base_url', 'https://api.biteship.com'), '/');
        $this->timeout = (int) config('biteship.timeout', 30);
    }

    public function get(string $uri, array $query = []): array
    {
        $request = $this->buildRequest();

        if ($query !== []) {
            $request = $request->withQueryParameters($query);
        }

        return $this->handleResponse(
            $request->get($this->url($uri))
        );
    }

    public function post(string $uri, array $data = []): array
    {
        return $this->handleResponse(
            $this->buildRequest()->post($this->url($uri), $data)
        );
    }

    public function put(string $uri, array $data = []): array
    {
        return $this->handleResponse(
            $this->buildRequest()->put($this->url($uri), $data)
        );
    }

    public function delete(string $uri): array
    {
        return $this->handleResponse(
            $this->buildRequest()->delete($this->url($uri))
        );
    }

    private function buildRequest(): PendingRequest
    {
        return $this->http
            ->baseUrl($this->baseUrl)
            ->withHeaders(['authorization' => $this->apiKey])  // bukan Bearer
            ->timeout($this->timeout)
            ->acceptJson()
            ->asJson();
    }

    private function handleResponse(Response $response): array
    {
        return match (true) {
            $response->unauthorized() => throw AuthenticationException::invalidApiKey(),
            $response->status() === 429 => throw RateLimitException::tooManyRequests($response),
            $response->unprocessableEntity() => throw ValidationException::fromResponse($response),
            $response->failed() => throw ApiException::fromResponse($response),
            default => $response->json() ?? [],
        };
    }

    private function url(string $uri): string
    {
        return '/'.ltrim($uri, '/');
    }
}
