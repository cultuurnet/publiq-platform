<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

use App\Domain\Contacts\Contact;
use App\Domain\Coupons\Coupon;
use Illuminate\Support\Collection;
use Ramsey\Uuid\UuidInterface;

final class Integration
{
    /**
     * @param Contact[] $contacts
     * @param Collection<Coupon> $coupon
     */
    public function __construct(
        public readonly UuidInterface $id,
        public readonly IntegrationType $type,
        public readonly string $name,
        public readonly string $description,
        public readonly UuidInterface $subscriptionId,
        public readonly IntegrationStatus $status,
        public readonly array $contacts,
        public readonly Collection $coupon,
    ) {
    }
}
