<?php

declare(strict_types=1);

namespace Tests\Insightly\Listeners;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Domain\Coupons\Repositories\CouponRepository;
use App\Domain\Integrations\Events\IntegrationActivatedWithOrganization;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Organizations\Address;
use App\Domain\Organizations\Organization;
use App\Domain\Organizations\Repositories\OrganizationRepository;
use App\Domain\Subscriptions\Currency;
use App\Domain\Subscriptions\Repositories\SubscriptionRepository;
use App\Domain\Subscriptions\Subscription;
use App\Domain\Subscriptions\SubscriptionCategory;
use App\Insightly\InsightlyMapping;
use App\Insightly\Listeners\CreateProject;
use App\Insightly\Listeners\CreateProjectWithOrganization;
use App\Insightly\Objects\OpportunityStage;
use App\Insightly\Objects\OpportunityState;
use App\Insightly\Objects\ProjectStage;
use App\Insightly\Objects\ProjectState;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\MockInsightlyClient;
use Tests\TestCase;

final class CreateProjectWithOrganizationTest extends TestCase
{
    use MockInsightlyClient;

    private CreateProjectWithOrganization $createProjectWithOrganization;

    private IntegrationRepository&MockObject $integrationRepository;

    private ContactRepository&MockObject $contactRepository;

    private OrganizationRepository&MockObject $organizationRepository;

    private SubscriptionRepository&MockObject $subscriptionRepository;

    private CouponRepository&MockObject $couponRepository;

    private InsightlyMappingRepository&MockObject $insightlyMappingRepository;

    protected function setUp(): void
    {
        $this->mockCrmClient();

        $this->integrationRepository = $this->createMock(IntegrationRepository::class);
        $this->contactRepository = $this->createMock(ContactRepository::class);
        $this->organizationRepository = $this->createMock(OrganizationRepository::class);
        $this->subscriptionRepository = $this->createMock(SubscriptionRepository::class);
        $this->couponRepository = $this->createMock(CouponRepository::class);
        $this->insightlyMappingRepository = $this->createMock(InsightlyMappingRepository::class);

        $this->createProjectWithOrganization = new CreateProjectWithOrganization(
            new CreateProject(
                $this->insightlyClient,
                $this->integrationRepository,
                $this->contactRepository,
                $this->subscriptionRepository,
                $this->couponRepository,
                $this->insightlyMappingRepository
            ),
            $this->insightlyClient,
            $this->organizationRepository,
            $this->insightlyMappingRepository,
            $this->createMock(LoggerInterface::class)
        );

        parent::setUp();
    }

    public function test_it_creates_a_project_when_activating_with_an_organization(): void
    {
        $subscription = $this->givenThereIsASubscription();
        $integration = $this->givenThereIsAnIntegration($subscription->id);
        $organization = $this->givenThereIsAnOrganization($integration->id);
        $contacts = $this->givenThereAreContacts($integration->id);

        /**
         * @var InsightlyMapping $opportunityMapping
         * @var InsightlyMapping $organizationMapping
         * @var InsightlyMapping $technicalContactMapping
         * @var InsightlyMapping $functionalContactMapping
         */
        list($opportunityMapping,
            $organizationMapping,
            $technicalContactMapping,
            $functionalContactMapping) = $this->givenThereAreInsightlyMappings($integration, $organization, $contacts);

        $this->opportunityResource->expects($this->once())
            ->method('updateState')
            ->with($opportunityMapping->insightlyId, OpportunityState::OPEN);

        $this->opportunityResource->expects($this->once())
            ->method('updateStage')
            ->with($opportunityMapping->insightlyId, OpportunityStage::REQUEST);

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with($integration->id)
            ->willReturn($integration);

        $insightlyProjectId = 55;
        $this->projectResource->expects($this->once())
            ->method('create')
            ->with($integration)
            ->willReturn($insightlyProjectId);

        $this->insightlyMappingRepository->expects($this->once())
            ->method('save')
            ->with(
                new InsightlyMapping($integration->id, $insightlyProjectId, ResourceType::Project)
            );

        $this->projectResource->expects($this->once())
            ->method('updateState')
            ->with($insightlyProjectId, ProjectState::COMPLETED);

        $this->projectResource->expects($this->once())
            ->method('updateStage')
            ->with($insightlyProjectId, ProjectStage::LIVE);

        $this->couponRepository->expects($this->once())
            ->method('getByIntegrationId')
            ->willThrowException(new ModelNotFoundException());

        $this->projectResource->expects($this->once())
            ->method('updateSubscription')
            ->with($insightlyProjectId, $subscription, null);

        $this->projectResource->expects($this->once())
            ->method('linkOpportunity')
            ->with($insightlyProjectId, $opportunityMapping->insightlyId);

        $this->contactRepository->expects($this->once())
            ->method('getByIntegrationId')
            ->with($integration->id)
            ->willReturn($contacts);

        $this->projectResource->expects($this->exactly(2))
            ->method('linkContact')
            ->willReturnCallback(
                fn (int $actualInsightlyProjectId, int $actualInsightlyContactId, ContactType $actualContactType) =>
                match ([$actualInsightlyProjectId, $actualInsightlyContactId, $actualContactType]) {
                    [$insightlyProjectId, $technicalContactMapping->insightlyId, ContactType::Technical],
                    [$insightlyProjectId, $functionalContactMapping->insightlyId, ContactType::Functional] => null,
                    default => throw new \LogicException('Invalid arguments received'),
                }
            );

        $this->organizationRepository->expects($this->once())
            ->method('getByIntegrationId')
            ->with($integration->id)
            ->willReturn($organization);

        $this->projectResource->expects($this->once())
            ->method('linkOrganization')
            ->with($insightlyProjectId, $organizationMapping->insightlyId);

        $this->createProjectWithOrganization->handle(
            new IntegrationActivatedWithOrganization($integration->id)
        );
    }

    private function givenThereIsAnIntegration(UuidInterface $subscriptionId): Integration
    {
        $integration = new Integration(
            Uuid::uuid4(),
            IntegrationType::SearchApi,
            'My integration',
            'This is my integration',
            $subscriptionId,
            IntegrationStatus::Draft
        );

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with($integration->id)
            ->willReturn($integration);

        return $integration;
    }

    private function givenThereAreContacts(UuidInterface $integrationId): Collection
    {
        $contacts = [
            new Contact(
                Uuid::uuid4(),
                $integrationId,
                'an@mail.com',
                ContactType::Technical,
                'An',
                'Deraaf'
            ),
            new Contact(
                Uuid::uuid4(),
                $integrationId,
                'piet@mail.com',
                ContactType::Functional,
                'Piet',
                'Dedonder'
            ),
            new Contact(
                Uuid::uuid4(),
                $integrationId,
                'jan@mail.com',
                ContactType::Contributor,
                'Jan',
                'Desmet'
            ),
        ];

        $contactCollection = new Collection($contacts);

        $this->contactRepository->expects($this->once())
            ->method('getByIntegrationId')
            ->with($integrationId)
            ->willReturn($contactCollection);

        return $contactCollection;
    }

    private function givenThereIsAnOrganization(UuidInterface $integrationId): Organization
    {
        $organization = new Organization(
            Uuid::uuid4(),
            'Test Organization',
            'facturatie@publiq.be',
            'BE 0475 250 609',
            new Address(
                'Henegouwenkaai 41-43',
                '1080',
                'Brussel',
                'België'
            )
        );

        $this->organizationRepository->expects($this->once())
            ->method('getByIntegrationId')
            ->with($integrationId)
            ->willReturn($organization);

        return $organization;
    }

    private function givenThereAreInsightlyMappings(
        Integration $integration,
        Organization $organization,
        Collection $contacts
    ): Collection {
        $insightlyIntegrationMapping = new InsightlyMapping(
            $integration->id,
            11,
            ResourceType::Opportunity,
        );

        $insightlyOrganizationMapping = new InsightlyMapping(
            $organization->id,
            22,
            ResourceType::Opportunity,
        );

        $insightlyTechnicalContactMapping = new InsightlyMapping(
            $contacts[0]->id,
            33,
            ResourceType::Contact,
        );

        $insightlyFunctionalContactMapping = new InsightlyMapping(
            $contacts[1]->id,
            44,
            ResourceType::Contact,
        );

        $this->insightlyMappingRepository->expects($this->exactly(4))
            ->method('getByIdAndType')
            ->willReturnCallback(
                fn (UuidInterface $actualId, ResourceType $actualResourceType) =>
                match ([$actualId, $actualResourceType]) {
                    [$integration->id, ResourceType::Opportunity] => $insightlyIntegrationMapping,
                    [$organization->id, ResourceType::Organization] => $insightlyOrganizationMapping,
                    [$contacts[0]->id, ResourceType::Contact] => $insightlyTechnicalContactMapping,
                    [$contacts[1]->id, ResourceType::Contact] => $insightlyFunctionalContactMapping,
                    default => throw new \LogicException('Invalid arguments received'),
                }
            );

        return new Collection([
            $insightlyIntegrationMapping,
            $insightlyOrganizationMapping,
            $insightlyTechnicalContactMapping,
            $insightlyFunctionalContactMapping,
        ]);
    }

    private function givenThereIsASubscription(): Subscription
    {
        $subscription = new Subscription(
            Uuid::uuid4(),
            'Basic Plan',
            'Basic Plan description',
            SubscriptionCategory::Basic,
            IntegrationType::SearchApi,
            Currency::EUR,
            14.99,
            99.99
        );

        $this->subscriptionRepository->expects($this->once())
            ->method('getById')
            ->with($subscription->id)
            ->willReturn($subscription);

        return $subscription;
    }
}
