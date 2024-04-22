<?php

declare(strict_types=1);

namespace Tests\Auth0\Repositories;

use App\Auth0\Auth0Client;
use App\Auth0\Auth0Tenant;
use App\Auth0\Repositories\EloquentAuth0ClientRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class EloquentAuth0ClientRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentAuth0ClientRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EloquentAuth0ClientRepository();
    }

    public function test_it_can_save_one_or_more_clients(): void
    {
        $integrationId = Uuid::uuid4();

        $client1 = new Auth0Client(
            Uuid::uuid4(),
            $integrationId,
            'client-id-1',
            'client-secret-1',
            Auth0Tenant::Acceptance
        );
        $client2 = new Auth0Client(
            Uuid::uuid4(),
            $integrationId,
            'client-id-2',
            'client-secret-2',
            Auth0Tenant::Testing
        );
        $client3 = new Auth0Client(
            Uuid::uuid4(),
            $integrationId,
            'client-id-3',
            'client-secret-3',
            Auth0Tenant::Production
        );

        $this->repository->save($client1, $client2, $client3);

        $this->assertDatabaseHas('auth0_clients', [
            'integration_id' => $integrationId->toString(),
            'auth0_client_id' => 'client-id-1',
            'auth0_client_secret' => 'client-secret-1',
            'auth0_tenant' => 'acc',
        ]);
        $this->assertDatabaseHas('auth0_clients', [
            'integration_id' => $integrationId->toString(),
            'auth0_client_id' => 'client-id-2',
            'auth0_client_secret' => 'client-secret-2',
            'auth0_tenant' => 'test',
        ]);
        $this->assertDatabaseHas('auth0_clients', [
            'integration_id' => $integrationId->toString(),
            'auth0_client_id' => 'client-id-3',
            'auth0_client_secret' => 'client-secret-3',
            'auth0_tenant' => 'prod',
        ]);
    }

    public function test_it_can_get_all_clients_for_an_integration_id(): void
    {
        $integrationId = Uuid::uuid4();

        $client1 = new Auth0Client(
            Uuid::uuid4(),
            $integrationId,
            'client-id-1',
            'client-secret-1',
            Auth0Tenant::Acceptance
        );
        $client2 = new Auth0Client(
            Uuid::uuid4(),
            $integrationId,
            'client-id-2',
            'client-secret-2',
            Auth0Tenant::Testing
        );
        $client3 = new Auth0Client(
            Uuid::uuid4(),
            $integrationId,
            'client-id-3',
            'client-secret-3',
            Auth0Tenant::Production
        );

        $this->repository->save($client1, $client2, $client3);

        $expected = [$client1, $client2, $client3];
        $actual = $this->repository->getByIntegrationId($integrationId);

        sort($expected);
        sort($actual);

        $this->assertEquals($expected, $actual);
    }

    public function test_it_can_get_all_clients_for_multiple_integration_ids(): void
    {
        $firstIntegrationId = Uuid::uuid4();
        $secondIntegrationId = Uuid::uuid4();
        $integrationIds = [$firstIntegrationId, $secondIntegrationId];

        $tenants = [Auth0Tenant::Acceptance, Auth0Tenant::Testing, Auth0Tenant::Production];

        $clients = [];

        foreach ($tenants as $tenant) {
            foreach ($integrationIds as $integrationId) {
                $count = count($clients) + 1;

                $clients[] = new Auth0Client(
                    Uuid::uuid4(),
                    $integrationId,
                    'client-id-' . $count,
                    'client-secret-' . $count,
                    $tenant
                );
            }
        }

        $this->repository->save(...$clients);

        $expected = $clients;

        $actual = $this->repository->getByIntegrationIds($integrationIds);

        sort($expected);
        sort($actual);

        $this->assertEquals($expected, $actual);
    }

    public function test_it_doesnt_get_clients_for_unasked_integration_ids(): void
    {
        $firstIntegrationId = Uuid::uuid4();
        $secondIntegrationId = Uuid::uuid4();
        $integrationIds = [$firstIntegrationId, $secondIntegrationId];

        $tenants = [Auth0Tenant::Acceptance, Auth0Tenant::Testing, Auth0Tenant::Production];

        $clients = [];

        foreach ($tenants as $tenant) {
            foreach ($integrationIds as $integrationId) {
                $count = count($clients) + 1;

                $clients[] = new Auth0Client(
                    Uuid::uuid4(),
                    $integrationId,
                    'client-id-' . $count,
                    'client-secret-' . $count,
                    $tenant
                );
            }
        }

        $this->repository->save(...$clients);

        $noSecondIntegrationClients = array_filter(
            $clients,
            fn (Auth0Client $client) => !$client->integrationId->equals($secondIntegrationId)
        );

        $expected = $noSecondIntegrationClients;

        $actual = $this->repository->getByIntegrationIds([$firstIntegrationId]);

        sort($expected);
        sort($actual);

        $this->assertEquals($expected, $actual);
    }

    public function test_it_can_get_missing_tenants_by_integration_id(): void
    {
        $integrationId = Uuid::uuid4();

        $client1 = new Auth0Client(
            Uuid::uuid4(),
            $integrationId,
            'client-id-1',
            'client-secret-1',
            Auth0Tenant::Acceptance
        );
        $client2 = new Auth0Client(
            Uuid::uuid4(),
            $integrationId,
            'client-id-2',
            'client-secret-2',
            Auth0Tenant::Testing
        );

        $this->repository->save($client1, $client2);

        $this->assertEquals(
            [Auth0Tenant::Production],
            $this->repository->getMissingTenantsByIntegrationId($integrationId)
        );
    }
}
