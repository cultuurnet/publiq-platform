<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithoutEvents;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    use WithoutEvents;
    use RefreshDatabase;
}
