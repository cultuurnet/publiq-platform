<?php

declare(strict_types=1);

namespace App\Domain\Integrations;

use App\Mails\Template\TemplateName;
use Ramsey\Uuid\UuidInterface;

final readonly class IntegrationMail
{
    public function __construct(
        public UuidInterface $uuid,
        public UuidInterface $integrationId,
        public TemplateName $templateName,
    ) {
    }
}
