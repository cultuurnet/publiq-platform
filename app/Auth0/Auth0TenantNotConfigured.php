<?php

declare(strict_types=1);

namespace App\Auth0;

use Exception;

final class Auth0TenantNotConfigured extends Exception
{
    public function __construct(Auth0Tenant $auth0Tenant)
    {
        parent::__construct('No configuration found for Auth0 tenant ' . $auth0Tenant->value);
    }
}
