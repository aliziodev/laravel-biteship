<?php

namespace Aliziodev\Biteship\Tests;

use Aliziodev\Biteship\BiteshipServiceProvider;
use Aliziodev\Biteship\Facades\Biteship;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            BiteshipServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return [
            'Biteship' => Biteship::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('biteship.api_key', 'biteship_test.test_key');
        $app['config']->set('biteship.cache.enabled', false); // default off di test
        $app['config']->set('cache.default', 'array');
    }

    /** Load fixture JSON dari tests/Fixtures */
    protected function fixture(string $name): array
    {
        $path = __DIR__."/Fixtures/{$name}.json";

        return json_decode(file_get_contents($path), true);
    }
}
