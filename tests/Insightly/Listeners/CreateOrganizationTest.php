<?php

declare(strict_types=1);

namespace Tests\Insightly\Listeners;

use App\Domain\Organizations\Address;
use App\Domain\Organizations\Events\OrganizationCreated;
use App\Domain\Organizations\Organization;
use App\Domain\Organizations\Repositories\OrganizationRepository;
use App\Insightly\InsightlyMapping;
use App\Insightly\Listeners\CreateOrganization;
use App\Insightly\Pipelines;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\MockInsightlyClient;
use Tests\UuidFactoryTest;

final class CreateOrganizationTest extends TestCase
{
    use MockInsightlyClient;

    private OrganizationRepository&MockObject $organizationRepository;

    private InsightlyMappingRepository&MockObject $insightlyMappingRepository;

    private CreateOrganization $listener;

    private UuidInterface $insightlyMappingOrganizationId;

    protected function setUp(): void
    {
        $this->organizationRepository = $this->createMock(OrganizationRepository::class);
        $this->insightlyMappingRepository = $this->createMock(InsightlyMappingRepository::class);

        $this->insightlyMappingOrganizationId = Uuid::fromString('bc7b8e68-263c-4008-a1a5-19563ca9ad8f');

        $pipelines = new Pipelines(['opportunities' => ['id' => 3, 'stages' => ['test' => 4]]]);
        $this->mockCrmClient($pipelines);
        $this->listener = new CreateOrganization(
            $this->insightlyClient,
            $this->organizationRepository,
            $this->insightlyMappingRepository,
            $this->createMock(LoggerInterface::class),
            new UuidFactoryTest($this->insightlyMappingOrganizationId)
        );
    }

    public function test_it_creates_an_organization(): void
    {
        // Given
        $organizationId = Uuid::uuid4();
        $insightlyId = 1234;
        $organization = $this->givenThereIsAnOrganization($organizationId);

        // Then it creates the organization at Insightly
        $this->organizationResource->expects($this->once())
            ->method('create')
            ->with($organization)
            ->willReturn($insightlyId);

        // Then it stores the insightly id in a mapping
        $insightlyIntegrationMapping = new InsightlyMapping(
            $this->insightlyMappingOrganizationId,
            $organizationId,
            $insightlyId,
            ResourceType::Organization,
        );
        $this->insightlyMappingRepository->expects(self::once())
            ->method('save')
            ->with($insightlyIntegrationMapping);

        // When
        $event = new OrganizationCreated($organizationId);
        $this->listener->handle($event);
    }

    private function givenThereIsAnOrganization(UuidInterface $id): Organization
    {
        $organization = new Organization(
            $id,
            'Test Organization',
            'BE 0475 250 609',
            'facturatie@publiq.be',
            new Address(
                'Henegouwenkaai 41-43',
                '1080',
                'Brussel',
                'België'
            )
        );
        $this->organizationRepository->expects($this->once())
            ->method('getById')
            ->with($organization->id)
            ->willReturn($organization);

        return $organization;
    }
}
