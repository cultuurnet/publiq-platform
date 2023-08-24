<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

use App\Auth0\Models\Auth0ClientModel;
use App\Domain\Contacts\Contact;
use App\Domain\Organizations\Organization;
use Ramsey\Uuid\UuidInterface;

final class Integration
{
    /** @var array<Contact> */
    private array $contacts;

    /** @var array<Auth0ClientModel> */
    private array $auth0Clients;

    private ?Organization $organization;

    /** @var array<IntegrationUrl> */
    private array $urls;

    public function __construct(
        public readonly UuidInterface $id,
        public readonly IntegrationType $type,
        public readonly string $name,
        public readonly string $description,
        public readonly UuidInterface $subscriptionId,
        public readonly IntegrationStatus $status,
    ) {
        $this->contacts = [];
        $this->urls = [];
        $this->auth0Clients = [];
        $this->organization = null;
    }

    public function withContacts(Contact ...$contacts): self
    {
        $clone = clone $this;
        $clone->contacts = $contacts;
        return $clone;
    }

    public function withOrganisation(Organization $organization): self
    {
        $clone = clone $this;
        $clone->organization = $organization;
        return $clone;
    }

    public function withAuth0Clients(Auth0ClientModel ...$auth0Clients): self
    {
        $clone = clone $this;
        $clone->auth0Clients = $auth0Clients;
        return $clone;
    }

    /**
     * @return array<Contact>
     */
    public function contacts(): array
    {
        return $this->contacts;
    }

    public function organization(): ?Organization
    {
        return $this->organization;
    }

    /** @return array<Auth0ClientModel> */
    public function auth0Clients(): array
    {
        return $this->auth0Clients;
    }

    public function withUrls(IntegrationUrl ...$urls): self
    {
        $clone = clone $this;
        $clone->urls = $urls;
        return $clone;
    }

    /**
     * @return array<IntegrationUrl>
     */
    public function urls(): array
    {
        return $this->urls;
    }

    /**
     * @return array<IntegrationUrl>
     */
    public function urlsForTypeAndEnvironment(IntegrationUrlType $type, Environment $environment): array
    {
        return array_filter(
            $this->urls,
            fn (IntegrationUrl $url) => $url->type->value === $type->value && $url->environment->value === $environment->value
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'name' => $this->name,
            'description' => $this->description,
            'subscriptionId' => $this->subscriptionId,
            'status' => $this->status,
        ];
    }
}
