<?php

namespace App\Auth0\Repositories;

use App\Auth0\Auth0Client;
use App\Auth0\Auth0ClientsForIntegration;
use Ramsey\Uuid\UuidInterface;

interface Auth0ClientRepository
{
    public function save(Auth0Client ...$auth0Clients): void;
    public function getByIntegrationId(UuidInterface $integrationId): Auth0ClientsForIntegration;
}
