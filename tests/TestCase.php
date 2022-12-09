<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        $this->disableConfigExternalApis();
    }

    protected function tearDown(): void
    {
        $this->disableConfigExternalApis();
        parent::tearDown();
    }

    protected function disableConfigExternalApis(): void
    {
        app('config')->set('insightly.api_key', '');
        app('config')->set('auth0.tenants', []);
    }
}
