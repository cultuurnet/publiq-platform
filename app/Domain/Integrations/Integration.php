<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

use App\Domain\Contacts\Contact;
use App\Domain\Organizations\Organization;
use App\UiTiDv1\Models\UiTiDv1ConsumerModel;
use Ramsey\Uuid\UuidInterface;

final class Integration
{
    /** @var array<Contact> */
    private array $contacts;
    private ?Organization $organization;

    /** @var array<UiTiDv1ConsumerModel> */
    private array $uiTiDv1Consumers;

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
        $this->uiTiDv1Consumers = [];
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

    public function withUiTiDv1Consumers(UiTiDv1ConsumerModel ...$uiTiDv1ConsumerModel): self
    {
        $clone = clone $this;
        $clone->uiTiDv1Consumers = $uiTiDv1ConsumerModel;
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

    /** @return array<UiTiDv1ConsumerModel> */
    public function uiTiDv1Consumers(): array
    {
        return $this->uiTiDv1Consumers;
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
