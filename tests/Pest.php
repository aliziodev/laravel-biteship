<?php

use Aliziodev\Biteship\Tests\TestCase;

/** @mixin TestCase */
uses(TestCase::class)->in('Feature', 'Unit');

function loadFixture(string $name): array
{
    return test()->fixture($name);
}
