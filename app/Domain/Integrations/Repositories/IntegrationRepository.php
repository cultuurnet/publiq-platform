<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Integrations\Integration;
use App\Domain\Integrations\Models\IntegrationModel;

final class IntegrationRepository
{
    public function save(Integration $integration): void
    {
        IntegrationModel::query()->create([
            'id' => $integration->id->toString(),
            'type' => $integration->type,
            'name' => $integration->name,
            'description' => $integration->description,
            'subscription_id' => $integration->subscription->id->toString(),
        ]);
    }
}
