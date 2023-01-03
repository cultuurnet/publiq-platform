<?php

declare(strict_types=1);

namespace App\UiTiDv1;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;

final class UiTiDv1SDKException extends RuntimeException
{
    public static function forResponse(ResponseInterface $response): self
    {
        $statusCode = $response->getStatusCode();
        $body = $response->getBody()->getContents();

        return new self(
            'UiTiD v1 responded with status code ' . $statusCode . ' instead of 2xx. Response body: ' . $body,
            $statusCode
        );
    }
}
