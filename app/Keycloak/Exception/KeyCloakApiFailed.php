<?php

declare(strict_types=1);

namespace App\Keycloak\Exception;

use App\Keycloak\Client;
use App\Keycloak\RealmWithScopeConfig;
use Exception;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\UuidInterface;

final class KeyCloakApiFailed extends Exception
{
    public const COULD_NOT_FETCH_ACCESS_TOKEN = 1;
    public const UNEXPECTED_TOKEN_RESPONSE = 2;
    public const FAILED_TO_CREATE_CLIENT = 3;
    public const FAILED_TO_CREATE_CLIENT_WITH_RESPONSE = 4;
    public const FAILED_TO_ADD_SCOPE = 5;
    public const FAILED_TO_ADD_SCOPE_WITH_RESPONSE = 6;
    public const FAILED_TO_FETCH_CLIENT = 7;
    public const FAILED_TO_UPDATE_CLIENT = 8;
    public const FAILED_TO_RESET_SCOPE = 9;
    public const FAILED_TO_EXCHANGE_TOKEN = 10;
    public const INVALID_JWT_TOKEN = 11;

    private function __construct(
        string $message,
        int $code = 0
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

    public static function failedToCreateClientWithResponse(ResponseInterface $response): self
    {
        return new self(sprintf('Failed to create client (status code %d): %s', $response->getStatusCode(), $response->getBody()->getContents()), self::FAILED_TO_CREATE_CLIENT_WITH_RESPONSE);
    }

    public static function failedToAddScopeToClient(string $message): self
    {
        return new self(sprintf('Failed to add scope to client: %s', $message), self::FAILED_TO_ADD_SCOPE);
    }

    public static function failedToAddScopeToClientWithResponse(ResponseInterface $response): self
    {
        return new self(sprintf('Failed to add scope to client (status code %d): %s', $response->getStatusCode(), $response->getBody()->getContents()), self::FAILED_TO_ADD_SCOPE_WITH_RESPONSE);
    }

    public static function failedToFetchClient(RealmWithScopeConfig $realm, string $body): self
    {
        return new self(sprintf('Failed to fetch client for realm %s: %s', $realm->internalName, $body), self::FAILED_TO_FETCH_CLIENT);
    }

    public static function failedToUpdateClient(Client $client): self
    {
        return new self(sprintf('Failed to update client %s', $client->id), self::FAILED_TO_UPDATE_CLIENT);
    }

    public static function failedToResetScope(Client $client, UuidInterface $scope): self
    {
        return new self(sprintf('Failed to reset scope for client %s, scope %s', $client->id, $scope->toString()), self::FAILED_TO_RESET_SCOPE);
    }

    public static function failedToResetScopeWithResponse(Client $client, UuidInterface $scope, string $body): self
    {
        return new self(sprintf('Failed to reset scope for client %s, scope %s: %s', $client->id, $scope->toString(), $body), self::FAILED_TO_RESET_SCOPE);
    }

    public static function failedToExchangeToken(string $body): self
    {
        return new self(sprintf('Failed to exchange token: %s', $body), self::FAILED_TO_EXCHANGE_TOKEN);
    }

    public static function invalidJwtToken(string $body): self
    {
        return new self(sprintf('Invalid JWT token: %s', $body), self::INVALID_JWT_TOKEN);
    }

    public static function noScopesConfigured(): self
    {
        return new self(sprintf('No scopes configured'), self::FAILED_TO_ADD_SCOPE);
    }
}
