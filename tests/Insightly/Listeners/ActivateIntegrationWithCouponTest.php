<?php

declare(strict_types=1);

namespace Tests\Insightly\Listeners;

use App\Domain\Integrations\Events\IntegrationActivatedWithCoupon;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Insightly\InsightlyMapping;
use App\Insightly\Listeners\ActivateIntegrationWithCoupon;
use App\Insightly\Objects\OpportunityStage;
use App\Insightly\Objects\OpportunityState;
use App\Insightly\Objects\ProjectStage;
use App\Insightly\Objects\ProjectState;
use App\Insightly\Resources\ResourceType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\MockInsightlyClient;
use Tests\MockRepositories;

final class ActivateIntegrationWithCouponTest extends TestCase
{
    use MockInsightlyClient;

    use MockRepositories;

    private ActivateIntegrationWithCoupon $listener;

    private IntegrationRepository&MockObject $integrationRepository;

    private UuidInterface $integrationId;

    private UuidInterface $contributorContactId;

    private UuidInterface $technicalContactId;

    private UuidInterface $functionalContactId;

    private string $couponCode;

    protected function setUp(): void
    {
        $this->mockRepositories();
        $this->integrationId = Uuid::uuid4();
        $this->contributorContactId = Uuid::uuid4();
        $this->technicalContactId = Uuid::uuid4();
        $this->functionalContactId = Uuid::uuid4();
        $this->couponCode = 'test123';

        $this->mockCrmClient();

        $this->listener = new ActivateIntegrationWithCoupon(
            $this->insightlyClient,
            $this->integrationRepository,
            $this->contactRepository,
            $this->insightlyMappingRepository,
            $this->couponRepository,
            $this->createMock(LoggerInterface::class),
        );
    }

    public function test_it_creates_a_project_when_activating_with_a_coupon(): void
    {
        // Given
        $insightlyOpportunityId = 42;
        $insightlyTechnicalId = 24;
        $insightlyFunctionalId = 15;
        $insightlyProjectId = 51;
        $integration = $this->givenThereIsAnIntegration($this->integrationId);
        $this->givenThereAreContacts($this->integrationId);
        $this->givenThereAreInsightlyMappings(
            $insightlyOpportunityId,
            $insightlyTechnicalId,
            $insightlyFunctionalId
        );
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

        // It sets the correct stage of the project
        $this->projectResource->expects($this->once())
            ->method('updateStage')
            ->with($insightlyProjectId, ProjectStage::LIVE);

        // It sets the correct state of the project
        $this->projectResource->expects($this->once())
            ->method('updateState')
            ->with($insightlyProjectId, ProjectState::COMPLETED);

        // It links the opportunity to the project
        $this->projectResource->expects($this->once())
            ->method('linkOpportunity')
            ->with($insightlyProjectId, $insightlyOpportunityId);

        // It links the contacts
        $this->projectResource->expects($this->exactly(2))
            ->method('linkContact')
            ->withConsecutive(
                [$insightlyProjectId, $insightlyTechnicalId],
                [$insightlyProjectId, $insightlyFunctionalId],
            );

        // When
        $event = new IntegrationActivatedWithCoupon($this->integrationId);
        $this->listener->handle($event);
    }
}
