<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

use App\Mails\Template\TemplateName;
use Ramsey\Uuid\UuidInterface;

final class IntegrationMail
{
    public function __construct(
        public readonly UuidInterface $integrationId,
        public readonly TemplateName $templateName,
    ) {
    }
}
