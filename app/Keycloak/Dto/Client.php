<?php

declare(strict_types=1);

namespace App\Keycloak\Dto;

final class Client
{
    private string $id;
    private string $clientId;
    private string $name;
    private string $description;
    private ?string $rootUrl;
    private ?string $adminUrl;
    private ?string $baseUrl;
    private bool $surrogateAuthRequired;
    private bool $enabled;
    private bool $alwaysDisplayInConsole;
    private string $clientAuthenticatorType;
    private ?string $secret;
    private array $redirectUris;
    private array $webOrigins;
    private int $notBefore;
    private bool $bearerOnly;
    private bool $consentRequired;
    private bool $standardFlowEnabled;
    private bool $implicitFlowEnabled;
    private bool $directAccessGrantsEnabled;
    private bool $serviceAccountsEnabled;
    private bool $publicClient;
    private bool $frontchannelLogout;
    private string $protocol;
    private array $attributes;
    private array $authenticationFlowBindingOverrides;
    private bool $fullScopeAllowed;
    private int $nodeReRegistrationTimeout;
    private array $protocolMappers;
    private array $defaultClientScopes;
    private array $optionalClientScopes;
    private array $access;

    private function __construct()
    {
    }

    public static function fromJson(array $data): self
    {
        $self = new self();
        $self->id = $data['id'];
        $self->clientId = $data['clientId'];
        $self->name = $data['name'];
        $self->description = $data['description'] ?? '';
        $self->rootUrl = $data['rootUrl'] ?? null;
        $self->adminUrl = $data['adminUrl'] ?? null;
        $self->baseUrl = $data['baseUrl'] ?? null;
        $self->surrogateAuthRequired = $data['surrogateAuthRequired'];
        $self->enabled = $data['enabled'];
        $self->alwaysDisplayInConsole = $data['alwaysDisplayInConsole'];
        $self->clientAuthenticatorType = $data['clientAuthenticatorType'];
        $self->secret = $data['secret'] ?? null;
        $self->redirectUris = $data['redirectUris'];
        $self->webOrigins = $data['webOrigins'];
        $self->notBefore = $data['notBefore'];
        $self->bearerOnly = $data['bearerOnly'];
        $self->consentRequired = $data['consentRequired'];
        $self->standardFlowEnabled = $data['standardFlowEnabled'];
        $self->implicitFlowEnabled = $data['implicitFlowEnabled'];
        $self->directAccessGrantsEnabled = $data['directAccessGrantsEnabled'];
        $self->serviceAccountsEnabled = $data['serviceAccountsEnabled'];
        $self->publicClient = $data['publicClient'];
        $self->frontchannelLogout = $data['frontchannelLogout'];
        $self->protocol = $data['protocol'];
        $self->attributes = $data['attributes'] ?? [];
        $self->authenticationFlowBindingOverrides = $data['authenticationFlowBindingOverrides'] ?? [];
        $self->fullScopeAllowed = $data['fullScopeAllowed'];
        $self->nodeReRegistrationTimeout = $data['nodeReRegistrationTimeout'];
        $self->protocolMappers = $data['protocolMappers'] ?? [];
        $self->defaultClientScopes = $data['defaultClientScopes'];
        $self->optionalClientScopes = $data['optionalClientScopes'] ?? [];
        $self->access = $data['access'] ?? [];

        return $self;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getRootUrl(): ?string
    {
        return $this->rootUrl;
    }

    public function getAdminUrl(): ?string
    {
        return $this->adminUrl;
    }

    public function getBaseUrl(): ?string
    {
        return $this->baseUrl;
    }

    public function isSurrogateAuthRequired(): bool
    {
        return $this->surrogateAuthRequired;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function isAlwaysDisplayInConsole(): bool
    {
        return $this->alwaysDisplayInConsole;
    }

    public function getClientAuthenticatorType(): string
    {
        return $this->clientAuthenticatorType;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function getRedirectUris(): array
    {
        return $this->redirectUris;
    }

    public function getWebOrigins(): array
    {
        return $this->webOrigins;
    }

    public function getNotBefore(): int
    {
        return $this->notBefore;
    }

    public function isBearerOnly(): bool
    {
        return $this->bearerOnly;
    }

    public function isConsentRequired(): bool
    {
        return $this->consentRequired;
    }

    public function isStandardFlowEnabled(): bool
    {
        return $this->standardFlowEnabled;
    }

    public function isImplicitFlowEnabled(): bool
    {
        return $this->implicitFlowEnabled;
    }

    public function isDirectAccessGrantsEnabled(): bool
    {
        return $this->directAccessGrantsEnabled;
    }

    public function isServiceAccountsEnabled(): bool
    {
        return $this->serviceAccountsEnabled;
    }

    public function isPublicClient(): bool
    {
        return $this->publicClient;
    }

    public function isFrontchannelLogout(): bool
    {
        return $this->frontchannelLogout;
    }

    public function getProtocol(): string
    {
        return $this->protocol;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAuthenticationFlowBindingOverrides(): array
    {
        return $this->authenticationFlowBindingOverrides;
    }

    public function isFullScopeAllowed(): bool
    {
        return $this->fullScopeAllowed;
    }

    public function getNodeReRegistrationTimeout(): int
    {
        return $this->nodeReRegistrationTimeout;
    }

    public function getProtocolMappers(): array
    {
        return $this->protocolMappers;
    }

    public function getDefaultClientScopes(): array
    {
        return $this->defaultClientScopes;
    }

    public function getOptionalClientScopes(): array
    {
        return $this->optionalClientScopes;
    }

    public function getAccess(): array
    {
        return $this->access;
    }
}
