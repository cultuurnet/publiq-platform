<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

use Ramsey\Uuid\UuidInterface;

final readonly class IntegrationOnHold
{
    public function __construct(public UuidInterface $id, public UuidInterface $integrationId, public string $comment, public bool $onHold)
    {
    }
}
