<?php

declare(strict_types=1);

namespace Tests\UiTiDv1\Repositories;

use App\UiTiDv1\Models\UiTiDv1ConsumerModel;
use App\UiTiDv1\Repositories\EloquentUiTiDv1ConsumerRepository;
use App\UiTiDv1\UiTiDv1Consumer;
use App\UiTiDv1\UiTiDv1Environment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\TestCase;

final class EloquentUiTiDv1ConsumerRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentUiTiDv1ConsumerRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentUiTiDv1ConsumerRepository();
    }

    public function test_it_can_get_all_consumers_for_multiple_integration_ids(): void
    {
        $firstIntegrationId = Uuid::uuid4();
        $secondIntegrationId = Uuid::uuid4();
        $integrationIds = [$firstIntegrationId, $secondIntegrationId];

        $consumers = $this->givenThereAreConsumersForEachEnvironment(...$integrationIds);

        $expected = $consumers;
        $actual = $this->repository->getByIntegrationIds($integrationIds);

        sort($expected);
        sort($actual);

        $this->assertEquals($expected, $actual);
    }

    public function test_it_doesnt_get_consumers_for_unasked_integration_ids(): void
    {
        $firstIntegrationId = Uuid::uuid4();
        $secondIntegrationId = Uuid::uuid4();

        $consumers = $this->givenThereAreConsumersForEachEnvironment($firstIntegrationId, $secondIntegrationId);

        $expected = array_filter(
            $consumers,
            fn (UiTiDv1Consumer $consumer) => !$consumer->integrationId->equals($secondIntegrationId)
        );

        $actual = $this->repository->getByIntegrationIds([$firstIntegrationId]);

        sort($expected);
        sort($actual);

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return UiTiDv1Consumer[]
     */
    private function givenThereAreConsumersForEachEnvironment(UuidInterface ...$integrationIds): array
    {
        $consumers = [];

        foreach (UiTiDv1Environment::cases() as $environment) {
            foreach ($integrationIds as $integrationId) {
                $count = count($consumers) + 1;

                $consumer = new UiTiDv1Consumer(
                    Uuid::uuid4(),
                    $integrationId,
                    (string) $count,
                    'consumer-key-' . $count,
                    'consumer-secret-' . $count,
                    'api-key-' . $count,
                    $environment
                );

                UiTiDv1ConsumerModel::query()->create([
                    'id' => $consumer->id->toString(),
                    'integration_id' => $consumer->integrationId->toString(),
                    'consumer_id' => $consumer->consumerId,
                    'consumer_key' => $consumer->consumerKey,
                    'consumer_secret' => $consumer->consumerSecret,
                    'api_key' => $consumer->apiKey,
                    'environment' => $consumer->environment->value,
                ]);

                $consumers[] = $consumer;
            }
        }

        return $consumers;
    }
}
