<?php

declare(strict_types=1);

namespace App\Insightly\Listeners;

use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Insightly\InsightlyClient;
use Illuminate\Contracts\Queue\ShouldQueue;

final class CreateOpportunity implements ShouldQueue
{
    public function __construct(
        private readonly InsightlyClient $insightlyClient,
        private readonly IntegrationRepository $integrationRepository
    ) {
    }

    public function handle(IntegrationCreated $integrationCreated): void
    {
        $this->insightlyClient->opportunities()->create(
            $this->integrationRepository->getById($integrationCreated->id)
        );
    }
}
