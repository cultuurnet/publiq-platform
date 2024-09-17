<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Events;

use App\Mails\MailEnabled;
use Illuminate\Foundation\Events\Dispatchable;
use Ramsey\Uuid\UuidInterface;

final class IntegrationBlocked implements MailEnabled
{
    use Dispatchable;

    public function __construct(public readonly UuidInterface $id)
    {
    }
    public function getId(): UuidInterface
    {
        return $this->id;
    }
}
