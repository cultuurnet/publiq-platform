<?php

declare(strict_types=1);

namespace App\Insightly\Listeners;

use App\Domain\Coupons\Coupon;
use App\Domain\Coupons\Repositories\CouponRepository;
use App\Domain\Integrations\Events\IntegrationUpdated;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Subscriptions\Repositories\SubscriptionRepository;
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
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly CouponRepository $couponRepository,
        private readonly InsightlyMappingRepository $insightlyMappingRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handle(IntegrationUpdated $integrationUpdated): void
    {
        try {
            $integration = $this->integrationRepository->getById($integrationUpdated->id);

            $opportunityMapping = $this->insightlyMappingRepository->getByIdAndType(
                $integrationUpdated->id,
                ResourceType::Opportunity
            );

            $this->insightlyClient->opportunities()->update($opportunityMapping->insightlyId, $integration);
            $this->insightlyClient->opportunities()->updateSubscription(
                $opportunityMapping->insightlyId,
                $this->subscriptionRepository->getById($integration->subscriptionId),
                $this->fetchCoupon($integration)
            );

            $this->logger->info(
                'Opportunity updated',
                [
                    'domain' => 'insightly',
                    'integration_id' => $integrationUpdated->id->toString(),
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
                'integration_id' => $integrationUpdated->id->toString(),
                'exception' => $exception,
            ]
        );
    }

    private function fetchCoupon(Integration $integration): ?Coupon
    {
        try {
            return $this->couponRepository->getByIntegrationId($integration->id);
        } catch (ModelNotFoundException) {
            return null;
        }
    }
}
