<?php

declare(strict_types=1);

namespace App\Keycloak\Exception;

use App\Keycloak\Realm;
use Exception;
use Psr\Http\Message\ResponseInterface;

final class KeyCloakApiFailed extends Exception
{
    public const COULD_NOT_FETCH_ACCESS_TOKEN = 1;
    public const UNEXPECTED_TOKEN_RESPONSE = 2;
    public const FAILED_TO_CREATE_CLIENT = 3;

    private function __construct(
        string $message,
        int $code
    ) {
        parent::__construct($message, $code);
    }

    public static function couldNotFetchAccessToken(string $responseBody): self
    {
        return new self('Could not retrieve access token: ' . $responseBody, self::COULD_NOT_FETCH_ACCESS_TOKEN);
    }

    public static function unexpectedTokenResponse(): self
    {
        return new self('Unexpected token response body', self::UNEXPECTED_TOKEN_RESPONSE);
    }

    public static function failedToCreateClient(string $message): self
    {
        return new self(sprintf('Failed to create client: %s', $message), self::FAILED_TO_CREATE_CLIENT);
    }
}
