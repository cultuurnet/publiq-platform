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

    /**
     * @test
     */
    public function it_can_save_one_or_more_consumers(): void
    {
        $integrationId = Uuid::uuid4();

        $consumer1 = new UiTiDv1Consumer(
            $integrationId,
            '1',
            'consumer-key-1',
            'consumer-secret-1',
            'api-key-1',
            UiTiDv1Environment::Acceptance
        );
        $consumer2 = new UiTiDv1Consumer(
            $integrationId,
            '2',
            'consumer-key-2',
            'consumer-secret-2',
            'api-key-2',
            UiTiDv1Environment::Testing
        );
        $consumer3 = new UiTiDv1Consumer(
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

    /**
     * @test
     */
    public function it_can_get_all_consumers_for_an_integration_id(): void
    {
        $integrationId = Uuid::uuid4();

        $consumer1 = new UiTiDv1Consumer(
            $integrationId,
            '1',
            'consumer-key-1',
            'consumer-secret-1',
            'api-key-1',
            UiTiDv1Environment::Acceptance
        );
        $consumer2 = new UiTiDv1Consumer(
            $integrationId,
            '2',
            'consumer-key-2',
            'consumer-secret-2',
            'api-key-2',
            UiTiDv1Environment::Testing
        );
        $consumer3 = new UiTiDv1Consumer(
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
}
