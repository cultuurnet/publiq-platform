<?php

declare(strict_types=1);

namespace Tests\Insightly\Listeners;

use App\Domain\Organizations\Address;
use App\Domain\Organizations\Events\OrganizationUpdated;
use App\Domain\Organizations\Organization;
use App\Domain\Organizations\Repositories\OrganizationRepository;
use App\Insightly\InsightlyMapping;
use App\Insightly\Listeners\UpdateOrganization;
use App\Insightly\Pipelines;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\MockInsightlyClient;

final class UpdateOrganizationTest extends TestCase
{
    use MockInsightlyClient;

    private OrganizationRepository&MockObject $organizationRepository;

    private InsightlyMappingRepository&MockObject $insightlyMappingRepository;

    private UpdateOrganization $listener;

    protected function setUp(): void
    {
        $this->mockCrmClient(new Pipelines(['opportunities' => ['id' => 3, 'stages' => ['test' => 4]]]));
        $this->organizationRepository = $this->createMock(OrganizationRepository::class);
        $this->insightlyMappingRepository = $this->createMock(InsightlyMappingRepository::class);

        $this->listener = new UpdateOrganization(
            $this->insightlyClient,
            $this->organizationRepository,
            $this->insightlyMappingRepository,
            $this->createMock(LoggerInterface::class),
        );
    }

    public function test_it_updates_an_organization(): void
    {
        // Given
        $organizationId = Uuid::uuid4();
        $insightlyId = 1234;

        $organization = $this->givenThereIsAnOrganization($organizationId);
        $this->givenTheInsightlyIdForTheIntegrationIs($insightlyId, $organizationId);

        // Then it updates the organization at Insightly
        $this->organizationResource->expects($this->once())
            ->method('update')
            ->with($organization, $insightlyId);

        // When
        $event = new OrganizationUpdated($organization->id);
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

    private function givenTheInsightlyIdForTheIntegrationIs(int $insightlyId, UuidInterface $organizationId): void
    {
        $insightlyIntegrationMapping = new InsightlyMapping(
            $organizationId,
            $insightlyId,
            ResourceType::Organization,
        );
        $this->insightlyMappingRepository->expects(self::once())
            ->method('getByIdAndType')
            ->with($organizationId, ResourceType::Organization)
            ->willReturn($insightlyIntegrationMapping);
    }
}
