<?php

declare(strict_types=1);

namespace Tests\Insightly\Listeners;

use App\Domain\Organizations\Address;
use App\Domain\Organizations\Events\OrganizationCreated;
use App\Domain\Organizations\Organization;
use App\Domain\Organizations\Repositories\OrganizationRepository;
use App\Insightly\InsightlyClient;
use App\Insightly\InsightlyMapping;
use App\Insightly\Listeners\CreateOrganization;
use App\Insightly\Pipelines;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use App\Json;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

final class CreateOrganizationTest extends TestCase
{
    private ClientInterface&MockObject $client;

    private OrganizationRepository&MockObject $organizationRepository;

    private InsightlyMappingRepository&MockObject $insightlyMappingRepository;

    private CreateOrganization $listener;

    protected function setUp(): void
    {
        $this->client = $this->createMock(ClientInterface::class);
        $this->organizationRepository = $this->createMock(OrganizationRepository::class);
        $this->insightlyMappingRepository = $this->createMock(InsightlyMappingRepository::class);

        $this->listener = new CreateOrganization(
            new InsightlyClient(
                $this->client,
                'api-key',
                new Pipelines(['opportunities'=>['id' => 3, 'stages' => ['test'=> 4]]])
            ),
            $this->organizationRepository,
            $this->insightlyMappingRepository,
            $this->createMock(LoggerInterface::class),
        );
    }

    public function test_it_creates_an_organization(): void
    {
        $organization = new Organization(
            Uuid::uuid4(),
            'Test Organization',
            'BE 0475 250 609',
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

        $insightlyId = 1234;
        $this->client->expects($this->once())
            ->method('sendRequest')
            ->willReturn(
                new Response(
                    200,
                    [],
                    Json::encode(['ORGANISATION_ID' => $insightlyId])
                ),
            );

        $insightlyIntegrationMapping = new InsightlyMapping(
            $organization->id,
            $insightlyId,
            ResourceType::Organization,
        );
        $this->insightlyMappingRepository->expects(self::once())
            ->method('save')
            ->with($insightlyIntegrationMapping);

        $event = new OrganizationCreated($organization->id);
        $this->listener->handle($event);
    }
}
