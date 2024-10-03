<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Events;

use App\Mails\Template\TemplateName;
use Illuminate\Foundation\Events\Dispatchable;
use Ramsey\Uuid\UuidInterface;

final readonly class ActivationExpired
{
    use Dispatchable;

    public function __construct(public UuidInterface $id, public TemplateName $templateName)
    {
    }
}
