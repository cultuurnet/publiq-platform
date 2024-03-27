<?php

declare(strict_types=1);

namespace App\Insightly\Listeners;

use App\Domain\Coupons\Repositories\CouponRepository;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Subscriptions\Repositories\SubscriptionRepository;
use App\Insightly\InsightlyClient;
use App\Insightly\InsightlyMapping;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Psr\Log\LoggerInterface;

final class CreateOpportunity implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly InsightlyClient $insightlyClient,
        private readonly IntegrationRepository $integrationRepository,
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly CouponRepository $couponRepository,
        private readonly InsightlyMappingRepository $insightlyMappingRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(IntegrationCreated $integrationCreated): void
    {
        $integration = $this->integrationRepository->getById($integrationCreated->id);

        $insightlyId = $this->insightlyClient->opportunities()->create($integration);

        $this->insightlyMappingRepository->save(new InsightlyMapping(
            $integrationCreated->id,
            $insightlyId,
            ResourceType::Opportunity
        ));

        try {
            $coupon = $this->couponRepository->getByIntegrationId($integration->id);
        } catch (ModelNotFoundException) {
            $coupon = null;
        }

        $this->insightlyClient->opportunities()->updateSubscription(
            $insightlyId,
            $this->subscriptionRepository->getById($integration->subscriptionId),
            $coupon
        );

        $this->logger->info(
            'Opportunity created for integration',
            [
                'domain' => 'insightly',
                'integration_id' => $integrationCreated->id->toString(),
            ]
        );
    }

    public function failed(IntegrationCreated $integrationCreated, \Throwable $exception): void
    {
        $this->logger->error(
            'Failed to create opportunity for integration',
            [
                'domain' => 'insightly',
                'integration_id' => $integrationCreated->id->toString(),
                'exception' => $exception,
            ]
        );
    }
}
