<?php

declare(strict_types=1);

namespace Tests;

use App\Domain\Contacts\Models\ContactModel;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Organizations\Models\OrganizationModel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        ContactModel::unsetEventDispatcher();
        IntegrationModel::unsetEventDispatcher();
        OrganizationModel::unsetEventDispatcher();
    }
}
