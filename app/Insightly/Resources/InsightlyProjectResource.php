<?php

declare(strict_types=1);

namespace App\Insightly\Resources;

use App\Domain\Integrations\Integration;
use App\Insightly\InsightlyClient;
use App\Insightly\Objects\ProjectStage;

final class InsightlyProjectResource implements ProjectResource
{
    private string $path = 'Projects/';

    public function __construct(
        private readonly InsightlyClient $insightlyClient,
    ) {
    }

    public function create(Integration $integration): int
    {
        return 0;
    }

    public function delete(int $id): void
    {
    }

    public function updateStage(int $id, ProjectStage $stage): void
    {
    }
}
