<?php

declare(strict_types=1);

namespace App\Auth0;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final class Auth0SDKException extends RuntimeException
{
    public static function forResponse(ResponseInterface $response): self
    {
        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();

        return new self(
            'Auth0 responded with status code ' . $statusCode . ' instead of 201. Response body: ' . $body,
            $statusCode
        );
    }
}
