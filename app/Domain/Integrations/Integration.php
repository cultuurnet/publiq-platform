<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

use App\Domain\Contacts\Contact;
use Ramsey\Uuid\UuidInterface;

final class Integration
{
    /** @var array<Contact> */
    private array $contacts;

    public function __construct(
        public readonly UuidInterface $id,
        public readonly IntegrationType $type,
        public readonly string $name,
        public readonly string $description,
        public readonly UuidInterface $subscriptionId,
        public readonly IntegrationStatus $status,
    ) {
        $this->contacts = [];
    }

    /**
     * @param array<Contact> $contacts
     */
    public function withContacts(array $contacts): self
    {
        $clone = clone $this;
        $clone->contacts = $contacts;
        return $clone;
    }

    public function contacts(): array
    {
        return $this->contacts;
    }
}
