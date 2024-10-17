<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Integrations\IntegrationMail;

interface IntegrationMailRepository
{
    public function create(IntegrationMail $integrationMail): void;
}
