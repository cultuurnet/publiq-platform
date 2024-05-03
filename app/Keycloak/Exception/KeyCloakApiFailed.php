<?php

declare(strict_types=1);

namespace App\Keycloak\Exception;

use App\Keycloak\Dto\Realm;
use Exception;

final class KeyCloakApiFailed extends Exception
{
    public const COULD_NOT_FETCH_ACCESS_TOKEN = 1;
    public const UNEXPECTED_TOKEN_RESPONSE = 2;
    public const IS_DISABLED = 3;
    public const FAILED_TO_FETCH_CLIENT = 4;

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

    public static function isDisabled(): self
    {
        return new self('Keycloak API is disabled.', self::IS_DISABLED);
    }

    public static function failedToFetchClient(Realm $realm, string $body): self
    {
        return new self(sprintf('Failed to fetch clients by realm %s: %s', $realm->getInternalName(), $body), self::FAILED_TO_FETCH_CLIENT);
    }
}
