<?php

declare(strict_types=1);

namespace Tests\Insightly\Listeners;

use App\Domain\Coupons\Repositories\CouponRepository;
use App\Domain\Integrations\Events\IntegrationUpdated;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Subscriptions\Currency;
use App\Domain\Subscriptions\Repositories\SubscriptionRepository;
use App\Domain\Subscriptions\Subscription;
use App\Domain\Subscriptions\SubscriptionCategory;
use App\Insightly\InsightlyMapping;
use App\Insightly\Listeners\UpdateProject;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tests\MockInsightlyClient;

final class UpdateProjectTest extends TestCase
{
    use MockInsightlyClient;

    private IntegrationRepository&MockObject $integrationRepository;

    private InsightlyMappingRepository&MockObject $insightlyMappingRepository;

    private UpdateProject $updateProject;
    private SubscriptionRepository&MockObject $subscriptionRepository;
    private CouponRepository&MockObject $couponRepository;

    protected function setUp(): void
    {
        $this->integrationRepository = $this->createMock(IntegrationRepository::class);
        $this->insightlyMappingRepository = $this->createMock(InsightlyMappingRepository::class);
        $this->subscriptionRepository = $this->createMock(SubscriptionRepository::class);
        $this->couponRepository = $this->createMock(CouponRepository::class);

        $this->mockCrmClient();

        $this->updateProject = new UpdateProject(
            $this->insightlyClient,
            $this->integrationRepository,
            $this->subscriptionRepository,
            $this->couponRepository,
            $this->insightlyMappingRepository,
            $this->createMock(LoggerInterface::class)
        );
    }

    public function test_it_updates_a_project(): void
    {
        $subscriptionId = Uuid::uuid4();

        $integration = new Integration(
            $subscriptionId,
            IntegrationType::SearchApi,
            'Test Integration',
            'Test Integration description',
            $subscriptionId,
            IntegrationStatus::Draft
        );

        $subscription = new Subscription(
            $subscriptionId,
            'free',
            'free',
            SubscriptionCategory::Free,
            IntegrationType::SearchApi,
            Currency::EUR,
            0.0,
            null
        );

        $insightlyMapping = new InsightlyMapping(
            $integration->id,
            11,
            ResourceType::Project
        );

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with($integration->id)
            ->willReturn($integration);

        $this->insightlyMappingRepository->expects($this->once())
            ->method('getByIdAndType')
            ->with($integration->id, ResourceType::Project)
            ->willReturn($insightlyMapping);

        $this->projectResource->expects($this->once())
            ->method('update')
            ->with($insightlyMapping->insightlyId, $integration);

        $this->projectResource->expects($this->once())
            ->method('updateSubscription')
            ->with($insightlyMapping->insightlyId, $subscription, null);

        $this->subscriptionRepository->expects($this->once())
            ->method('getById')
            ->with($subscriptionId)
            ->willReturn($subscription);

        $this->couponRepository->expects($this->once())
            ->method('getByIntegrationId')
            ->with($integration->id)
            ->willThrowException(new ModelNotFoundException());

        $this->updateProject->handle(new IntegrationUpdated($integration->id));
    }
}
