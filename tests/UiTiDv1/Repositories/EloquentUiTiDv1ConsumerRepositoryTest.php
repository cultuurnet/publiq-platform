<?php

declare(strict_types=1);

namespace Tests\UiTiDv1\Repositories;

use App\UiTiDv1\Repositories\EloquentUiTiDv1ConsumerRepository;
use App\UiTiDv1\UiTiDv1Consumer;
use App\UiTiDv1\UiTiDv1Environment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
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

    public function test_it_can_save_one_or_more_consumers(): void
    {
        $integrationId = Uuid::uuid4();

        $consumer1 = new UiTiDv1Consumer(
            Uuid::uuid4(),
            $integrationId,
            '1',
            'consumer-key-1',
            'consumer-secret-1',
            'api-key-1',
            UiTiDv1Environment::Acceptance
        );
        $consumer2 = new UiTiDv1Consumer(
            Uuid::uuid4(),
            $integrationId,
            '2',
            'consumer-key-2',
            'consumer-secret-2',
            'api-key-2',
            UiTiDv1Environment::Testing
        );
        $consumer3 = new UiTiDv1Consumer(
            Uuid::uuid4(),
            $integrationId,
            '3',
            'consumer-key-3',
            'consumer-secret-3',
            'api-key-3',
            UiTiDv1Environment::Production
        );

        $this->repository->save($consumer1, $consumer2, $consumer3);

        $this->assertDatabaseHas('uitidv1_consumers', [
            'integration_id' => $integrationId->toString(),
            'consumer_id' => '1',
            'consumer_key' => 'consumer-key-1',
            'consumer_secret' => 'consumer-secret-1',
            'api_key' => 'api-key-1',
            'environment' => 'acc',
        ]);
        $this->assertDatabaseHas('uitidv1_consumers', [
            'integration_id' => $integrationId->toString(),
            'consumer_id' => '2',
            'consumer_key' => 'consumer-key-2',
            'consumer_secret' => 'consumer-secret-2',
            'api_key' => 'api-key-2',
            'environment' => 'test',
        ]);
        $this->assertDatabaseHas('uitidv1_consumers', [
            'integration_id' => $integrationId->toString(),
            'consumer_id' => '3',
            'consumer_key' => 'consumer-key-3',
            'consumer_secret' => 'consumer-secret-3',
            'api_key' => 'api-key-3',
            'environment' => 'prod',
        ]);
    }

    public function test_it_can_get_all_consumers_for_an_integration_id(): void
    {
        $integrationId = Uuid::uuid4();

        $consumer1 = new UiTiDv1Consumer(
            Uuid::uuid4(),
            $integrationId,
            '1',
            'consumer-key-1',
            'consumer-secret-1',
            'api-key-1',
            UiTiDv1Environment::Acceptance
        );
        $consumer2 = new UiTiDv1Consumer(
            Uuid::uuid4(),
            $integrationId,
            '2',
            'consumer-key-2',
            'consumer-secret-2',
            'api-key-2',
            UiTiDv1Environment::Testing
        );
        $consumer3 = new UiTiDv1Consumer(
            Uuid::uuid4(),
            $integrationId,
            '3',
            'consumer-key-3',
            'consumer-secret-3',
            'api-key-3',
            UiTiDv1Environment::Production
        );

        $this->repository->save($consumer1, $consumer2, $consumer3);

        $expected = [$consumer1, $consumer2, $consumer3];
        $actual = $this->repository->getByIntegrationId($integrationId);

        sort($expected);
        sort($actual);

        $this->assertEquals($expected, $actual);
    }

    public function test_it_can_get_all_consumers_for_multiple_integration_ids(): void
    {
        $firstIntegrationId = Uuid::uuid4();
        $secondIntegrationId = Uuid::uuid4();
        $integrationIds = [$firstIntegrationId, $secondIntegrationId];

        $environments = [UiTiDv1Environment::Acceptance, UiTiDv1Environment::Testing, UiTiDv1Environment::Production];

        $consumers = [];

        foreach ($environments as $environment) {
            foreach ($integrationIds as $integrationId) {
                $count = count($consumers) + 1;

                $consumers[] = new UiTiDv1Consumer(
                    Uuid::uuid4(),
                    $integrationId,
                    (string)$count,
                    'consumer-key-' . $count,
                    'consumer-secret-' . $count,
                    'api-key-' . $count,
                    $environment
                );
            }
        }

        $this->repository->save(...$consumers);

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
        $integrationIds = [$firstIntegrationId, $secondIntegrationId];

        $environments = [UiTiDv1Environment::Acceptance, UiTiDv1Environment::Testing, UiTiDv1Environment::Production];

        $consumers = [];

        foreach ($environments as $environment) {
            foreach ($integrationIds as $integrationId) {
                $count = count($consumers) + 1;

                $consumers[] = new UiTiDv1Consumer(
                    Uuid::uuid4(),
                    $integrationId,
                    (string)$count,
                    'consumer-key-' . $count,
                    'consumer-secret-' . $count,
                    'api-key-' . $count,
                    $environment
                );
            }
        }

        $this->repository->save(...$consumers);

        $noSecondIntegrationConsumers = array_filter(
            $consumers,
            fn (UiTiDv1Consumer $consumer) => !$consumer->integrationId->equals($secondIntegrationId)
        );

        $expected = $noSecondIntegrationConsumers;

        $actual = $this->repository->getByIntegrationIds([$firstIntegrationId]);

        sort($expected);
        sort($actual);

        $this->assertEquals($expected, $actual);
    }

    public function test_it_can_get_missing_environments(): void
    {
        $integrationId = Uuid::uuid4();

        $consumer1 = new UiTiDv1Consumer(
            Uuid::uuid4(),
            $integrationId,
            '1',
            'consumer-key-1',
            'consumer-secret-1',
            'api-key-1',
            UiTiDv1Environment::Acceptance
        );
        $consumer2 = new UiTiDv1Consumer(
            Uuid::uuid4(),
            $integrationId,
            '2',
            'consumer-key-2',
            'consumer-secret-2',
            'api-key-2',
            UiTiDv1Environment::Testing
        );

        $this->repository->save($consumer1, $consumer2);

        $this->assertEquals(
            [UiTiDv1Environment::Production],
            $this->repository->getMissingEnvironmentsByIntegrationId($integrationId)
        );
    }
}
