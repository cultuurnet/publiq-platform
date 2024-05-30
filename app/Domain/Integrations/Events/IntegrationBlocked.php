<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Events;

use App\Domain\Integrations\IntegrationStatus;
use Illuminate\Foundation\Events\Dispatchable;
use Ramsey\Uuid\UuidInterface;

final class IntegrationBlocked
{
    use Dispatchable;

    public function __construct(
        public readonly UuidInterface $id
        //public readonly IntegrationStatus $previousStatus
    ) {
    }
}
