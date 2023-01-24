<?php

declare(strict_types=1);

namespace App\Insightly\Listeners;

use App\Domain\Integrations\Events\IntegrationActivatedWithCoupon;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Insightly\InsightlyClient;
use App\Insightly\InsightlyMapping;
use App\Insightly\Objects\OpportunityStage;
use App\Insightly\Objects\OpportunityState;
use App\Insightly\Objects\ProjectStage;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class ActivateIntegrationWithCoupon implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly InsightlyClient $insightlyClient,
        private readonly IntegrationRepository $integrationRepository,
        private readonly InsightlyMappingRepository $insightlyMappingRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(IntegrationActivatedWithCoupon $integrationActivatedWithCoupon): void
    {
        $integration = $this->integrationRepository->getById($integrationActivatedWithCoupon->id);
        $insightlyOpportunityMapping = $this->insightlyMappingRepository->getByIdAndType($integration->id, ResourceType::Opportunity);

        $this->insightlyClient->opportunities()->updateStage(
            $insightlyOpportunityMapping->insightlyId,
            OpportunityStage::CLOSED
        );

        $this->insightlyClient->opportunities()->updateState(
            $insightlyOpportunityMapping->insightlyId,
            OpportunityState::WON
        );

        $insightlyProjectId = $this->insightlyClient->projects()->create($integration);

        $this->insightlyMappingRepository->save(new InsightlyMapping(
            $integrationActivatedWithCoupon->id,
            $insightlyProjectId,
            ResourceType::Project
        ));

        $this->insightlyClient->projects()->updateStage($insightlyProjectId, ProjectStage::TEST); //TODO: Should be live, check why it does not work.

        $this->insightlyClient->projects()->linkOpportunity(
            $insightlyProjectId,
            $insightlyOpportunityMapping->insightlyId
        );

        $this->logger->info(
            'Project created for integration',
            [
                'domain' => 'insightly',
                'integration_id' => $integrationActivatedWithCoupon->id->toString(),
            ]
        );
    }

    public function failed(IntegrationActivatedWithCoupon $integrationActivatedWithCoupon, \Throwable $exception): void
    {
        $this->logger->error(
            'Failed to activate integration',
            [
                'domain' => 'insightly',
                'contact_id' => $integrationActivatedWithCoupon->id->toString(),
                'exception' => $exception,
            ]
        );
    }
}
