<?php

declare(strict_types=1);

namespace App\Keycloak\Client;

use App\Keycloak\Realm;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface HttpClient
{
    public function sendWithoutBearer(RequestInterface $request, Realm $realm): ResponseInterface;

    /** @throws GuzzleException */
    public function sendWithBearer(RequestInterface $request, Realm $realm): ResponseInterface;
}
