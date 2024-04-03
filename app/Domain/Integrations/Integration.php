<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

use App\Auth0\Auth0Client;
use App\Domain\Contacts\Contact;
use App\Domain\Coupons\Coupon;
use App\Domain\Organizations\Organization;
use App\UiTiDv1\UiTiDv1Consumer;
use Ramsey\Uuid\UuidInterface;

final class Integration
{
    private KeyVisibility $keyVisibility;

    /** @var array<Contact> */
    private array $contacts;

    /** @var array<Auth0Client> */
    private array $auth0Clients;

    private ?Organization $organization;

    /** @var array<UiTiDv1Consumer> */
    private array $uiTiDv1Consumers;

    /** @var array<IntegrationUrl> */
    private array $urls;

    private ?Coupon $coupon;

    public function __construct(
        public readonly UuidInterface $id,
        public readonly IntegrationType $type,
        public readonly string $name,
        public readonly string $description,
        public readonly UuidInterface $subscriptionId,
        public readonly IntegrationStatus $status,
        public readonly IntegrationPartnerStatus $partnerStatus
    ) {
        $this->contacts = [];
        $this->urls = [];
        $this->uiTiDv1Consumers = [];
        $this->auth0Clients = [];
        $this->organization = null;
        $this->keyVisibility = KeyVisibility::v2;
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

    public function withAuth0Clients(Auth0Client ...$auth0Clients): self
    {
        $clone = clone $this;
        $clone->auth0Clients = $auth0Clients;
        return $clone;
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

    /** @return array<UiTiDv1Consumer> */
    public function uiTiDv1Consumers(): array
    {
        return $this->uiTiDv1Consumers;
    }

    /** @return array<Auth0Client> */
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
            'partnerStatus' => $this->partnerStatus,
            'keyVisibility' => $this->keyVisibility,
            'coupon' => isset($this->coupon) ? $this->coupon : null,
        ];
    }
}
