<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Integrations\IntegrationMail;
use App\Domain\Integrations\Models\IntegrationMailModel;

final class EloquentIntegrationMailRepository implements IntegrationMailRepository
{
    public function create(IntegrationMail $integrationMail): void
    {
        IntegrationMailModel::query()->create(
            [
                'uuid' => $integrationMail->uuid,
                'integration_id' => $integrationMail->integrationId->toString(),
                'template_name' => $integrationMail->templateName,
            ]
        );
    }
}
