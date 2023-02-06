<?php

declare(strict_types=1);

namespace Tests\Insightly\Listeners;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Domain\Coupons\Coupon;
use App\Domain\Coupons\Repositories\CouponRepository;
use App\Domain\Integrations\Events\IntegrationActivatedWithCoupon;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Insightly\InsightlyMapping;
use App\Insightly\Listeners\CreateProjectWithCoupon;
use App\Insightly\Objects\OpportunityStage;
use App\Insightly\Objects\OpportunityState;
use App\Insightly\Objects\ProjectStage;
use App\Insightly\Objects\ProjectState;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use Illuminate\Support\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\MockInsightlyClient;

final class CreateProjectWithCouponTest extends TestCase
{
    use MockInsightlyClient;

    private CreateProjectWithCoupon $listener;

    private IntegrationRepository&MockObject $integrationRepository;

    private ContactRepository&MockObject $contactRepository;

    private InsightlyMappingRepository&MockObject $insightlyMappingRepository;

    private CouponRepository&MockObject $couponRepository;

    private UuidInterface $integrationId;

    private UuidInterface $contributorContactId;

    private UuidInterface $technicalContactId;

    private UuidInterface $functionalContactId;

    private string $couponCode;

    protected function setUp(): void
    {
        $this->integrationId = Uuid::uuid4();
        $this->contributorContactId = Uuid::uuid4();
        $this->technicalContactId = Uuid::uuid4();
        $this->functionalContactId = Uuid::uuid4();
        $this->couponCode = 'test123';
        $this->integrationRepository = $this->createMock(IntegrationRepository::class);
        $this->contactRepository = $this->createMock(ContactRepository::class);
        $this->insightlyMappingRepository = $this->createMock(InsightlyMappingRepository::class);
        $this->couponRepository = $this->createMock(CouponRepository::class);

        $this->mockCrmClient();

        $this->listener = new CreateProjectWithCoupon(
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
        $integration = $this->givenThereIsAnIntegrationWithId($this->integrationId);
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

    private function givenThereAreInsightlyMappings(
        int $insightlyOpportunityId,
        int $insightlyTechnicalId,
        int $insightlyFunctionalId
    ): void {
        $insightlyIntegrationMapping = new InsightlyMapping(
            $this->integrationId,
            $insightlyOpportunityId,
            ResourceType::Opportunity,
        );

        $insightlyTechnicalContactMapping = new InsightlyMapping(
            $this->technicalContactId,
            $insightlyTechnicalId,
            ResourceType::Contact,
        );

        $insightlyFunctionalContactMapping = new InsightlyMapping(
            $this->functionalContactId,
            $insightlyFunctionalId,
            ResourceType::Contact,
        );

        $this->insightlyMappingRepository->expects($this->exactly(3))
            ->method('getByIdAndType')
            ->withConsecutive(
                [$this->integrationId, ResourceType::Opportunity],
                [$this->technicalContactId, ResourceType::Contact],
                [$this->functionalContactId, ResourceType::Contact],
            )
            ->willReturnOnConsecutiveCalls(
                $insightlyIntegrationMapping,
                $insightlyTechnicalContactMapping,
                $insightlyFunctionalContactMapping,
            );
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

    private function givenThereAreContacts(UuidInterface $integrationId): Collection
    {
        $contacts = [
            new Contact(
                $this->contributorContactId,
                $integrationId,
                'jan@mail.com',
                ContactType::Contributor,
                'Jan',
                'Desmet'
            ),
            new Contact(
                $this->technicalContactId,
                $integrationId,
                'an@mail.com',
                ContactType::Technical,
                'An',
                'Deraaf'
            ),
            new Contact(
                $this->functionalContactId,
                $integrationId,
                'piet@mail.com',
                ContactType::Functional,
                'Piet',
                'Dedonder'
            ),
        ];

        $contactCollection = new Collection($contacts);

        $this->contactRepository->expects($this->once())
            ->method('getByIntegrationId')
            ->with($integrationId)
            ->willReturn($contactCollection);

        return $contactCollection;
    }
}
