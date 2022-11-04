<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

use App\Domain\Contacts\Contact;
use App\Domain\Subscriptions\Subscription;
use Ramsey\Uuid\UuidInterface;

final class Integration
{
    /**
     * @param Contact[] $contacts
     */
    public function __construct(
        public readonly UuidInterface $id,
        public readonly IntegrationType $type,
        public readonly string $name,
        public readonly string $description,
        public readonly Subscription $subscription,
        public readonly array $contacts,
    ) {
    }
}
