<?php

declare(strict_types=1);

namespace Tests\Keycloak\Repositories;

use App\Keycloak\Client;
use App\Keycloak\Config;
use App\Keycloak\Realm;
use App\Keycloak\RealmCollection;
use App\Keycloak\Repositories\EloquentKeycloakClientRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Tests\Keycloak\ConfigFactory;
use Tests\Keycloak\RealmFactory;
use Tests\TestCase;

final class EloquentKeycloakClientRepositoryTest extends TestCase
{
    use RefreshDatabase;
    use ConfigFactory;
    use RealmFactory;

    private EloquentKeycloakClientRepository $repository;
    private Config $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = new EloquentKeycloakClientRepository();
        $this->config = $this->givenKeycloakConfig();
        $this->configureKeycloakConfigFacade();
    }

    public function test_it_can_save_one_or_more_clients(): void
    {
        $integrationId = Uuid::uuid4();
        $clientId = Uuid::uuid4();
        $clientId2 = Uuid::uuid4();

        $client1 = new Client(
            Uuid::uuid4(),
            $integrationId,
            $clientId,
            'client-secret-1',
            $this->givenAcceptanceRealm()
        );
        $client2 = new Client(
            Uuid::uuid4(),
            $integrationId,
            $clientId2,
            'client-secret-2',
            $this->givenTestRealm()
        );
        $this->repository->create($client1, $client2);

        $this->assertDatabaseHas('keycloak_clients', [
            'integration_id' => $integrationId->toString(),
            'client_secret' => 'client-secret-1',
            'client_id' => $clientId->toString(),
            'realm' => $this->givenAcceptanceRealm()->publicName,
        ]);
        $this->assertDatabaseHas('keycloak_clients', [
            'integration_id' => $integrationId->toString(),
            'client_secret' => 'client-secret-2',
            'client_id' => $clientId2->toString(),
            'realm' => $this->givenTestRealm()->publicName,
        ]);
    }

    public function test_it_can_get_all_clients_for_an_integration_id(): void
    {
        /** @var Realm $realm */
        $realm = $this->config->realms->first();

        $integrationId = Uuid::uuid4();
        $clientId = Uuid::uuid4();
        $clientId2 = Uuid::uuid4();

        $client1 = new Client(
            Uuid::uuid4(),
            $integrationId,
            $clientId,
            'client-secret-1',
            $realm
        );
        $client2 = new Client(
            Uuid::uuid4(),
            $integrationId,
            $clientId2,
            'client-secret-1',
            $this->givenAcceptanceRealm()
        );
        $this->repository->create($client1, $client2);

        $expected = [$client1, $client2];
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

        $realms = [$this->givenAcceptanceRealm(), $this->givenTestRealm()];

        $clients = [];

        foreach ($realms as $realm) {
            foreach ($integrationIds as $integrationId) {
                $count = count($clients) + 1;

                $clients[] = new Client(
                    Uuid::uuid4(),
                    $integrationId,
                    Uuid::uuid4(),
                    'client-secret-' . $count,
                    $realm
                );
            }
        }

        $this->repository->create(...$clients);

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

        $clients = [];
        foreach ($this->config->realms as $realm) {
            foreach ($integrationIds as $integrationId) {
                $count = count($clients) + 1;

                $clients[] = new Client(
                    Uuid::uuid4(),
                    $integrationId,
                    Uuid::uuid4(),
                    'client-secret-' . $count,
                    $realm
                );
            }
        }

        $this->repository->create(...$clients);

        $noSecondIntegrationClients = array_filter(
            $clients,
            fn (Client $client) => !$client->integrationId->equals($secondIntegrationId)
        );

        $expected = $noSecondIntegrationClients;

        $actual = $this->repository->getByIntegrationIds([$firstIntegrationId]);

        sort($expected);
        sort($actual);

        $this->assertEquals($expected, $actual);
    }

    public function test_it_can_get_missing_realms_by_integration_id(): void
    {
        $integrationId = Uuid::uuid4();
        $clients = [];

        $missing = new RealmCollection();
        foreach ($this->config->realms as $realm) {
            if ($missing->isEmpty()) {
                $missing->add($realm);
                continue;
            }

            $clients[] = new Client(
                Uuid::uuid4(),
                $integrationId,
                Uuid::uuid4(),
                'client-secret',
                $realm
            );
        }
        $this->repository->create(...$clients);

        $this->assertEquals(
            $missing,
            $this->repository->getMissingRealmsByIntegrationId($integrationId, $this->config->realms)
        );
    }
}
