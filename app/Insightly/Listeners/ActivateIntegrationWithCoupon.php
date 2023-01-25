<?php

declare(strict_types=1);

namespace App\Insightly\Listeners;

use App\Domain\Integrations\Events\IntegrationActivatedWithCoupon;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Insightly\InsightlyClient;
use App\Insightly\InsightlyMapping;
use App\Insightly\Objects\OpportunityStage;
use App\Insightly\Objects\OpportunityState;
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
        $insightlyOpportunityMapping = $this->insightlyMappingRepository->getByIdAndType(
            $integrationActivatedWithCoupon->id,
            ResourceType::Opportunity
        );

        $this->insightlyClient->opportunities()->updateStage(
            $insightlyOpportunityMapping->insightlyId,
            OpportunityStage::CLOSED
        );

        $this->insightlyClient->opportunities()->updateState(
            $insightlyOpportunityMapping->insightlyId,
            OpportunityState::WON
        );

        $integration = $this->integrationRepository->getById($integrationActivatedWithCoupon->id);
        $insightlyProjectId = $this->insightlyClient->projects()->create($integration);

        $this->insightlyMappingRepository->save(new InsightlyMapping(
            $integrationActivatedWithCoupon->id,
            $insightlyProjectId,
            ResourceType::Project
        ));

        // TODO: Set correct state and stage on project

        $this->insightlyClient->projects()->linkOpportunity(
            $insightlyProjectId,
            $insightlyOpportunityMapping->insightlyId
        );

        // TODO: Links contacts

        $this->logger->info(
            'Project created for integration activated with coupon',
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
