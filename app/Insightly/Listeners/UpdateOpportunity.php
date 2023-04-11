<?php

declare(strict_types=1);

namespace App\Insightly\Listeners;

use App\Domain\Integrations\Events\IntegrationUpdated;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Insightly\InsightlyClient;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Psr\Log\LoggerInterface;

final class UpdateOpportunity implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly InsightlyClient $insightlyClient,
        private readonly IntegrationRepository $integrationRepository,
        private readonly InsightlyMappingRepository $insightlyMappingRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handle(IntegrationUpdated $integrationUpdated): void
    {
        try {
            $integration = $this->integrationRepository->getById($integrationUpdated->integrationId);

            $opportunityMapping = $this->insightlyMappingRepository->getByIdAndType(
                $integrationUpdated->integrationId,
                ResourceType::Opportunity
            );

            $this->insightlyClient->opportunities()->update($opportunityMapping->insightlyId, $integration);

            $this->logger->info(
                'Opportunity updated',
                [
                    'domain' => 'insightly',
                    'integration_id' => $integrationUpdated->integrationId->toString(),
                ]
            );
        } catch (ModelNotFoundException) {
        }
    }

    public function failed(IntegrationUpdated $integrationUpdated, \Throwable $exception): void
    {
        $this->logger->error(
            'Failed to update opportunity',
            [
                'domain' => 'insightly',
                'integration_id' => $integrationUpdated->integrationId->toString(),
                'exception' => $exception,
            ]
        );
    }
}
