<?php

declare(strict_types=1);

namespace Tests\Insightly\Listeners;

use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Insightly\InsightlyClient;
use App\Insightly\InsightlyMapping;
use App\Insightly\Listeners\CreateOpportunity;
use App\Insightly\Pipelines;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use App\Json;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class CreateOpportunityTest extends TestCase
{
    private ClientInterface&MockObject $client;

    private IntegrationRepository&MockObject $integrationRepository;

    private InsightlyMappingRepository&MockObject $insightlyMappingRepository;

    private CreateOpportunity $listener;

    protected function setUp(): void
    {
        $this->client = $this->createMock(ClientInterface::class);
        $this->integrationRepository = $this->createMock(IntegrationRepository::class);
        $this->insightlyMappingRepository = $this->createMock(InsightlyMappingRepository::class);

        $this->listener = new CreateOpportunity(
            new InsightlyClient(
                $this->client,
                'api-key',
                new Pipelines(['opportunities'=>['id' => 3, 'stages' => ['test'=> 4]]])
            ),
            $this->integrationRepository,
            $this->insightlyMappingRepository,
        );
    }

    /**
     * @test
     */
    public function it_creates_an_opportunity(): void
    {
        $integrationId = Uuid::uuid4();
        $insightlyId = 42;

        $this->givenThereIsAnIntegrationWithId($integrationId);

        $this->client->expects($this->exactly(2))
            ->method('sendRequest')
            ->willReturnOnConsecutiveCalls(
                new Response(200, [], Json::encode(['OPPORTUNITY_ID' => $insightlyId])),
                new Response(200, [])
            );

        $insightlyIntegrationMapping = new InsightlyMapping(
            $integrationId,
            $insightlyId,
            ResourceType::Opportunity,
        );

        $this->insightlyMappingRepository->expects(self::once())
            ->method('save')
            ->with($insightlyIntegrationMapping);

        $event = new IntegrationCreated($integrationId);
        $this->listener->handle($event);
    }

    private function givenThereIsAnIntegrationWithId(UuidInterface $integrationId): void
    {
        $integration = new Integration(
            $integrationId,
            IntegrationType::EntryApi,
            'my little integration',
            'a little integration',
            Uuid::uuid4(),
            IntegrationStatus::Draft,
            [],
        );

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->with($integrationId)
            ->willReturn($integration);
    }
}
