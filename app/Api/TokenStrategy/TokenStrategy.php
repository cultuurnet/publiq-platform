<?php

declare(strict_types=1);

namespace App\Api\TokenStrategy;

use App\Api\ClientCredentialsContext;

interface TokenStrategy
{
    public function fetchToken(ClientCredentialsContext $context): string;
    public function clearToken(ClientCredentialsContext $context): void;
}
