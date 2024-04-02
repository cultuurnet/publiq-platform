<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();
        Event::fake();
    }
}
