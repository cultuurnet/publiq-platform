<?php

declare(strict_types=1);

namespace App\Insightly\Listeners;

use App\Domain\Integrations\Events\IntegrationBlocked;
use App\Insightly\InsightlyClient;
use App\Insightly\Objects\OpportunityState;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Psr\Log\LoggerInterface;
use Throwable;

final class BlockOpportunity implements ShouldQueue
{
    public function __construct(
        private readonly InsightlyClient $insightlyClient,
        private readonly InsightlyMappingRepository $insightlyMappingRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handle(IntegrationBlocked $integrationBlocked): void
    {
        try {
            $integrationId = $integrationBlocked->id;

            $insightlyMapping = $this->insightlyMappingRepository->getByIdAndType(
                $integrationId,
                ResourceType::Opportunity
            );

            $this->insightlyClient->opportunities()->updateState(
                $insightlyMapping->insightlyId,
                OpportunityState::SUSPENDED
            );

            $this->logger->info(
                'Opportunity blocked',
                [
                    'domain' => 'insightly',
                    'integration_id' => $integrationId,
                ]
            );
        } catch (ModelNotFoundException) {
        }
    }

    public function failed(
        IntegrationBlocked $integrationBlocked,
        Throwable $exception
    ): void {
        $this->logger->error(
            'Failed to block opportunity',
            [
                'domain' => 'insightly',
                'contact_id' => $integrationBlocked->id->toString(),
                'exception' => $exception,
            ]
        );
    }
}
