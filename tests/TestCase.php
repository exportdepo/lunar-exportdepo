<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\LaravelBlink\BlinkFacade as Blink;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Lunar caches "default" model lookups (Currency::getDefault(), Channel::getDefault(), ...)
        // in Blink, which is not reset by RefreshDatabase between tests.
        Blink::flush();
    }
}
