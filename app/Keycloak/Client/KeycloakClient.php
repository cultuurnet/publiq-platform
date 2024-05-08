<?php

declare(strict_types=1);

namespace App\Keycloak\Client;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface KeycloakClient
{
    public function send(RequestInterface $request): ResponseInterface;
}
