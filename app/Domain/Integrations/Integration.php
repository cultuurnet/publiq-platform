<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Coupons\Coupon;
use App\Domain\Integrations\Exceptions\KeycloakClientNotFound;
use App\Domain\KeyVisibilityUpgrades\KeyVisibilityUpgrade;
use App\Domain\Organizations\Organization;
use App\Domain\Subscriptions\Subscription;
use App\Keycloak\Client;
use App\Keycloak\Client as KeycloakClient;
use App\UiTiDv1\UiTiDv1Consumer;
use App\UiTiDv1\UiTiDv1Environment;
use Ramsey\Uuid\UuidInterface;

final class Integration
{
    private KeyVisibility $keyVisibility;

    private ?KeyVisibilityUpgrade $keyVisibilityUpgrade;

    /** @var array<Contact> */
    private array $contacts;

    /** @var array<KeycloakClient> */
    private array $keycloakClients;

    private ?Organization $organization;

    /** @var array<UdbOrganizer> */
    private array $udbOrganizers;

    /** @var array<UiTiDv1Consumer> */
    private array $uiTiDv1Consumers;

    /** @var array<IntegrationUrl> */
    private array $urls;

    private ?Subscription $subscription;

    private ?Website $website;

    private ?Coupon $coupon;

    public function __construct(
        public readonly UuidInterface $id,
        public readonly IntegrationType $type,
        public readonly string $name,
        public readonly string $description,
        public readonly UuidInterface $subscriptionId,
        public readonly IntegrationStatus $status,
        public readonly IntegrationPartnerStatus $partnerStatus,
    ) {
        $this->contacts = [];
        $this->urls = [];
        $this->uiTiDv1Consumers = [];
        $this->keycloakClients = [];
        $this->udbOrganizers = [];
        $this->organization = null;
        $this->keyVisibility = KeyVisibility::v2;
        $this->keyVisibilityUpgrade = null;
        $this->subscription = null;
        $this->website = null;
        $this->coupon = null;
    }

    public function withKeyVisibility(KeyVisibility $keyVisibility): self
    {
        $clone = clone $this;
        $clone->keyVisibility = $keyVisibility;
        return $clone;
    }

    public function getKeyVisibility(): KeyVisibility
    {
        return $this->keyVisibility;
    }

    public function withKeyVisibilityUpgrade(KeyVisibilityUpgrade $keyVisibilityUpgrade): self
    {
        $clone = clone $this;
        $clone->keyVisibilityUpgrade = $keyVisibilityUpgrade;
        return $clone;
    }

    public function getKeyVisibilityUpgrade(): ?KeyVisibilityUpgrade
    {
        return $this->keyVisibilityUpgrade;
    }

    public function withContacts(Contact ...$contacts): self
    {
        $clone = clone $this;
        $clone->contacts = $contacts;
        return $clone;
    }

    public function withOrganization(Organization $organization): self
    {
        $clone = clone $this;
        $clone->organization = $organization;
        return $clone;
    }

    public function withUiTiDv1Consumers(UiTiDv1Consumer ...$uiTiDv1ConsumerModel): self
    {
        $clone = clone $this;
        $clone->uiTiDv1Consumers = $uiTiDv1ConsumerModel;
        return $clone;
    }

    public function withKeycloakClients(KeycloakClient ...$keycloakClients): self
    {
        $clone = clone $this;
        $clone->keycloakClients = $keycloakClients;
        return $clone;
    }

    public function withUdbOrganizers(UdbOrganizer ...$organizers): self
    {
        $clone = clone $this;
        $clone->udbOrganizers = $organizers;
        return $clone;
    }

    public function withSubscription(Subscription $subscription): self
    {
        $clone = clone $this;
        $clone->subscription = $subscription;
        return $clone;
    }

    public function withWebsite(Website $website): self
    {
        $clone = clone $this;
        $clone->website = $website;
        return $clone;
    }

    public function website(): ?Website
    {
        return $this->website;
    }

    public function withCoupon(Coupon $coupon): self
    {
        $clone = clone $this;
        $clone->coupon = $coupon;
        return $clone;
    }

    /**
     * @return array<Contact>
     */
    public function contacts(): array
    {
        return $this->contacts;
    }

    public function contactHasAccess(string $email): bool
    {
        return collect($this->contacts)->contains(fn (Contact $contact) => $contact->email === $email);
    }

    public function organization(): ?Organization
    {
        return $this->organization;
    }

    /**
     * @return array<UdbOrganizer>
     */
    public function udbOrganizers(): array
    {
        return $this->udbOrganizers;
    }

    /** @return array<UiTiDv1Consumer> */
    public function uiTiDv1Consumers(): array
    {
        return $this->uiTiDv1Consumers;
    }

    /** @return array<KeycloakClient> */
    public function keycloakClients(): array
    {
        return $this->keycloakClients;
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
        // Wrapped this with array_values, so we don't retain the indexes
        return array_values(array_filter(
            $this->urls,
            fn (IntegrationUrl $url) => $url->type->value === $type->value && $url->environment->value === $environment->value
        ));
    }

    public function isKeyVisibleForEnvironment(UiTiDv1Environment|Environment $environment): bool
    {
        $keyVisibility = ($environment instanceof UiTiDv1Environment) ? KeyVisibility::v2 : KeyVisibility::v1;
        return $environment->value !== 'acc' &&
            $this->status !== IntegrationStatus::Deleted &&
            $this->getKeyVisibility() !== $keyVisibility;
    }

    /** @throws KeycloakClientNotFound */
    public function getKeycloakClientByEnv(Environment $environment): Client
    {
        foreach ($this->keycloakClients() as $client) {
            if ($client->environment === $environment) {
                return $client;
            }
        }

        throw KeycloakClientNotFound::byEnvironment($environment);
    }

    /**
     * To avoid spamming, we prevent sending the same email multiple times to the same address.
     * Some email content varies by recipient type, so we prioritize sending versions with the preferred type when possible.
     * @return Contact[]
     */
    public function filterUniqueContactsWithPreferredContactType(ContactType $contactType): array
    {
        $uniqueContacts = [];

        foreach ($this->contacts() as $contact) {
            if (!isset($uniqueContacts[$contact->email]) || $contact->type === $contactType) {
                $uniqueContacts[$contact->email] = $contact;
            }
        }

        return $uniqueContacts;
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
            'partnerStatus' => $this->partnerStatus,
            'keyVisibility' => $this->keyVisibility,
            'keyVisibilityUpgrade' => $this->keyVisibilityUpgrade,
            'contacts' => $this->contacts,
            'urls' => $this->urls,
            'organization' => $this->organization,
            'organizers' => $this->udbOrganizers,
            'authClients' => $this->keycloakClients,
            'legacyAuthConsumers' => $this->uiTiDv1Consumers,
            'keycloakClients' => $this->keycloakClients,
            'subscription' => $this->subscription,
            'website' => $this->website->value ?? null,
            'coupon' => $this->coupon ?? null,
        ];
    }
}
