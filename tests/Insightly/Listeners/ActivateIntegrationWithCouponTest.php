<?php

declare(strict_types=1);

namespace Tests\Insightly\Listeners;

use App\Domain\Coupons\Coupon;
use App\Domain\Coupons\Repositories\CouponRepository;
use App\Domain\Integrations\Events\IntegrationActivatedWithCoupon;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Insightly\InsightlyMapping;
use App\Insightly\Listeners\ActivateIntegrationWithCoupon;
use App\Insightly\Objects\OpportunityStage;
use App\Insightly\Objects\OpportunityState;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\MockInsightlyClient;

final class ActivateIntegrationWithCouponTest extends TestCase
{
    use MockInsightlyClient;

    private ActivateIntegrationWithCoupon $listener;

    private IntegrationRepository&MockObject $integrationRepository;

    private InsightlyMappingRepository&MockObject $insightlyMappingRepository;

    private CouponRepository&MockObject $couponRepository;

    private UuidInterface $integrationId;

    private string $couponCode;

    protected function setUp(): void
    {
        $this->integrationId = Uuid::uuid4();
        $this->couponCode = 'test123';
        $this->integrationRepository = $this->createMock(IntegrationRepository::class);
        $this->insightlyMappingRepository = $this->createMock(InsightlyMappingRepository::class);
        $this->couponRepository = $this->createMock(CouponRepository::class);

        $this->mockCrmClient();

        $this->listener = new ActivateIntegrationWithCoupon(
            $this->insightlyClient,
            $this->integrationRepository,
            $this->insightlyMappingRepository,
            $this->couponRepository,
            $this->createMock(LoggerInterface::class),
        );
    }

    public function test_it_creates_a_project_when_activating_with_a_coupon(): void
    {
        // Given
        $insightlyOpportunityId = 42;
        $insightlyProjectId = 51;
        $integration = $this->givenThereIsAnIntegrationWithId($this->integrationId);
        $this->givenThereIsAnOpportunityMapping($this->integrationId);
        $coupon = $this->givenThereIsACoupon($this->integrationId);

        // Then
        // It updates the stage of the opportunity
        $this->opportunityResource->expects($this->once())
            ->method('updateStage')
            ->with($insightlyOpportunityId, OpportunityStage::CLOSED);

        // It updates the state of the opportunity
        $this->opportunityResource->expects($this->once())
            ->method('updateState')
            ->with($insightlyOpportunityId, OpportunityState::WON);

        // It creates the project at Insightly
        $this->projectResource->expects($this->once())
            ->method('create')
            ->with($integration)
            ->willReturn($insightlyProjectId);

        // It updates the project with a coupon code
        $this->projectResource->expects($this->once())
            ->method('updateWithCoupon')
            ->with($insightlyProjectId, $coupon->code);

        // It stores the insightlyProjectId mapping
        $insightlyMapping = new InsightlyMapping(
            $this->integrationId,
            $insightlyProjectId,
            ResourceType::Project,
        );

        $this->insightlyMappingRepository->expects(self::once())
            ->method('save')
            ->with($insightlyMapping);

        // TODO: Test the correct state and stage on project

        // It links the opportunity to the project
        $this->projectResource->expects($this->once())
            ->method('linkOpportunity')
            ->with($insightlyProjectId, $insightlyOpportunityId);

        // TODO: Test the contacts

        // When
        $event = new IntegrationActivatedWithCoupon($this->integrationId);
        $this->listener->handle($event);
    }

    private function givenThereIsAnIntegrationWithId(UuidInterface $integrationId): Integration
    {
        $integration = new Integration(
            $this->integrationId,
            IntegrationType::SearchApi,
            'My integration',
            'This is my integration',
            Uuid::uuid4(),
            IntegrationStatus::Draft,
            []
        );

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with($integrationId)
            ->willReturn($integration);

        return $integration;
    }

    private function givenThereIsAnOpportunityMapping(UuidInterface $integrationId): InsightlyMapping
    {
        $insightlyMapping = new InsightlyMapping(
            $integrationId,
            42,
            ResourceType::Opportunity,
        );

        $this->insightlyMappingRepository->expects($this->once())
            ->method('getByIdAndType')
            ->with($this->integrationId, ResourceType::Opportunity)
            ->willReturn($insightlyMapping);

        return $insightlyMapping;
    }

    private function givenThereIsACoupon(UuidInterface $integrationId): Coupon
    {
        $coupon = new Coupon(
            Uuid::uuid4(),
            true,
            $integrationId,
            $this->couponCode,
        );

        $this->couponRepository->expects($this->once())
            ->method('getByIntegrationId')
            ->with($integrationId)
            ->willReturn($coupon);

        return $coupon;
    }
}
