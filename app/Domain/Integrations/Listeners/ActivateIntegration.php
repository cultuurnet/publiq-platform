<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Listeners;

use App\Domain\Coupons\Repositories\CouponRepository;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Subscriptions\Repositories\SubscriptionRepository;
use App\Domain\Subscriptions\SubscriptionCategory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Psr\Log\LoggerInterface;
use Throwable;

final class ActivateIntegration implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly CouponRepository $couponRepository,
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handle(IntegrationCreated $integrationCreated): void
    {
        $integration = $this->integrationRepository->getById($integrationCreated->id);

        try {
            $this->couponRepository->getByIntegrationId($integration->id);
        } catch (ModelNotFoundException) {
            return;
        }

        if ($integration->type === IntegrationType::EntryApi) {
            return;
        }

        $subscription = $this->subscriptionRepository->getById($integration->subscriptionId);
        if ($subscription->category !== SubscriptionCategory::Basic) {
            return;
        }

        $this->integrationRepository->activate($integration->id);
    }

    public function failed(IntegrationCreated $integrationCreated, Throwable $exception): void
    {
        $this->logger->error('Failed to activate integration', [
            'integration_id' => $integrationCreated->id->toString(),
            'exception' => $exception->getMessage(),
        ]);
    }
}
