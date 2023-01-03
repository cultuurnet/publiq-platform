<?php

declare(strict_types=1);

namespace App\Insightly\Serializers;

use App\Domain\Integrations\Integration;

final class ProjectSerializer
{
    public function __construct(private readonly Pipelines $pipelines)
    {
    }

    public function toInsightlyArray(Integration $integration): array
    {
        return [];
    }
}
