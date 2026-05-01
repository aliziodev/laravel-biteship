<?php

namespace Aliziodev\Biteship\Contracts;

interface BiteshipClientInterface
{
    public function get(string $uri, array $query = []): array;

    public function post(string $uri, array $data = []): array;

    public function put(string $uri, array $data = []): array;

    public function delete(string $uri): array;
}
