<?php

declare(strict_types=1);

namespace App\Insightly\Listeners;

use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Insightly\InsightlyClient;
use App\Insightly\InsightlyMapping;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

final class CreateOpportunity implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly InsightlyClient $insightlyClient,
        private readonly IntegrationRepository $integrationRepository,
        private readonly InsightlyMappingRepository $insightlyMappingRepository
    ) {
    }

    public function handle(IntegrationCreated $integrationCreated): void
    {
        if (empty(config('insightly.api_key'))) {
            return;
        }

        $insightlyId = $this->insightlyClient->opportunities()->create(
            $this->integrationRepository->getById($integrationCreated->id)
        );

        $this->insightlyMappingRepository->save(new InsightlyMapping(
            $integrationCreated->id,
            $insightlyId,
            ResourceType::Opportunity
        ));

        Log::info(
            'Opportunity created for integration',
            [
                'domain' => 'insightly',
                'integration_id' => $integrationCreated->id->toString(),
            ]
        );
    }

    public function failed(IntegrationCreated $integrationCreated, \Throwable $exception): void
    {
        Log::error(
            'Failed to create opportunity for integration',
            [
                'domain' => 'insightly',
                'integration_id' => $integrationCreated->id->toString(),
                'exception' => $exception,
            ]
        );
    }
}
