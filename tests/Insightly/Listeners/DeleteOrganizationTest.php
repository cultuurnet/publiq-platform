<?php

declare(strict_types=1);

namespace Tests\Insightly\Listeners;

use App\Domain\Organizations\Events\OrganizationDeleted;
use App\Insightly\InsightlyClient;
use App\Insightly\InsightlyMapping;
use App\Insightly\Listeners\DeleteOrganization;
use App\Insightly\Pipelines;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

final class DeleteOrganizationTest extends TestCase
{
    private ClientInterface&MockObject $client;

    private InsightlyMappingRepository&MockObject $insightlyMappingRepository;

    private DeleteOrganization $listener;

    protected function setUp(): void
    {
        $this->client = $this->createMock(ClientInterface::class);
        $this->insightlyMappingRepository = $this->createMock(InsightlyMappingRepository::class);

        $this->listener = new DeleteOrganization(
            new InsightlyClient(
                $this->client,
                'api-key',
                new Pipelines(['opportunities'=>['id' => 3, 'stages' => ['test'=> 4]]])
            ),
            $this->insightlyMappingRepository,
            $this->createMock(LoggerInterface::class),
        );
    }

    public function test_it_can_delete_an_organization(): void
    {
        $organizationId = Uuid::uuid4();
        $insightlyId = 1234;

        $insightlyIntegrationMapping = new InsightlyMapping(
            $organizationId,
            $insightlyId,
            ResourceType::Organization,
        );
        $this->insightlyMappingRepository->expects(self::once())
            ->method('getById')
            ->with($organizationId)
            ->willReturn($insightlyIntegrationMapping);

        $this->client->expects($this->once())
            ->method('sendRequest')
            ->with(
                $this->callback(
                    function (Request $request) use ($insightlyId): bool {
                        return $request->getMethod() === 'DELETE'
                            && $request->getUri()->getPath() === 'Organizations/' . $insightlyId;
                    }
                )
            );

        $event = new OrganizationDeleted($organizationId);
        $this->listener->handle($event);
    }
}
