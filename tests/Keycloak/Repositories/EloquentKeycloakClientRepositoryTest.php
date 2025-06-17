<?php

declare(strict_types=1);

namespace Tests\Keycloak\Repositories;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Events\IntegrationActivationRequested;
use App\Keycloak\Client;
use App\Keycloak\Events\ClientCreated;
use App\Keycloak\Realms;
use App\Keycloak\Repositories\EloquentKeycloakClientRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Ramsey\Uuid\Uuid;
use Tests\Keycloak\RealmFactory;
use Tests\TestCase;

final class EloquentKeycloakClientRepositoryTest extends TestCase
{
    use RefreshDatabase;

    use RealmFactory;

    private EloquentKeycloakClientRepository $repository;
    private Realms $realms;

    protected function setUp(): void
    {
        parent::setUp();

        $this->realms = $this->givenAllRealms();
        $this->repository = new EloquentKeycloakClientRepository($this->realms);
    }

    public function test_it_can_save_one_or_more_clients(): void
    {
        $integrationId = Uuid::uuid4();
        $clientId = Uuid::uuid4()->toString();
        $clientId2 = Uuid::uuid4()->toString();

        $client1 = new Client(
            Uuid::uuid4(),
            $integrationId,
            $clientId,
            'client-secret-1',
            Environment::Acceptance
        );
        $client2 = new Client(
            Uuid::uuid4(),
            $integrationId,
            $clientId2,
            'client-secret-2',
            Environment::Testing
        );
        $this->repository->create($client1, $client2);

        $this->assertDatabaseHas('keycloak_clients', [
            'integration_id' => $integrationId->toString(),
            'client_secret' => 'client-secret-1',
            'client_id' => $clientId,
            'realm' => Environment::Acceptance->value,
        ]);
        $this->assertDatabaseHas('keycloak_clients', [
            'integration_id' => $integrationId->toString(),
            'client_secret' => 'client-secret-2',
            'client_id' => $clientId2,
            'realm' => Environment::Testing->value,
        ]);

        Event::assertDispatched(ClientCreated::class);
    }

    public function test_it_can_get_all_clients_for_an_integration_id(): void
    {
        $integrationId = Uuid::uuid4();
        $clientId = Uuid::uuid4()->toString();
        $clientId2 = Uuid::uuid4()->toString();

        $client1 = new Client(
            Uuid::uuid4(),
            $integrationId,
            $clientId,
            'client-secret-1',
            Environment::Acceptance
        );
        $client2 = new Client(
            Uuid::uuid4(),
            $integrationId,
            $clientId2,
            'client-secret-1',
            Environment::Testing
        );
        $this->repository->create($client1, $client2);

        $expected = [$client1, $client2];
        $actual = $this->repository->getByIntegrationId($integrationId);

        sort($expected);
        sort($actual);

        $this->assertClientMatches($actual, $expected);
    }

    public function test_it_can_get_all_clients_for_multiple_integration_ids(): void
    {
        $firstIntegrationId = Uuid::uuid4();
        $secondIntegrationId = Uuid::uuid4();
        $integrationIds = [$firstIntegrationId, $secondIntegrationId];

        $realms = new Realms([$this->givenAcceptanceRealm(), $this->givenTestRealm()]);

        $clients = [];

        foreach ($realms as $realm) {
            foreach ($integrationIds as $integrationId) {
                $count = count($clients) + 1;

                $clients[] = new Client(
                    Uuid::uuid4(),
                    $integrationId,
                    Uuid::uuid4()->toString(),
                    'client-secret-' . $count,
                    $realm->environment
                );
            }
        }

        $this->repository->create(...$clients);

        $expected = $clients;

        $actual = $this->repository->getByIntegrationIds($integrationIds);

        sort($expected);
        sort($actual);

        $this->assertClientMatches($actual, $expected);
    }

    public function test_it_doesnt_get_clients_for_unasked_integration_ids(): void
    {
        $firstIntegrationId = Uuid::uuid4();
        $secondIntegrationId = Uuid::uuid4();
        $integrationIds = [$firstIntegrationId, $secondIntegrationId];

        $clients = [];
        foreach ($this->realms as $realm) {
            foreach ($integrationIds as $integrationId) {
                $count = count($clients) + 1;

                $clients[] = new Client(
                    Uuid::uuid4(),
                    $integrationId,
                    Uuid::uuid4()->toString(),
                    'client-secret-' . $count,
                    $realm->environment
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

        $this->assertClientMatches($actual, $expected);
    }

    public function test_it_can_get_missing_realms_by_integration_id(): void
    {
        $integrationId = Uuid::uuid4();
        $clients = [];

        $missingRealmCollection = new Realms([$this->givenAcceptanceRealm()]);
        foreach ($missingRealmCollection as $realm) {
            $clients[] = new Client(
                Uuid::uuid4(),
                $integrationId,
                Uuid::uuid4()->toString(),
                'client-secret',
                $realm->environment,
            );
        }
        $this->repository->create(...$clients);

        $missingEnvironments = $this->repository->getMissingEnvironmentsByIntegrationId($integrationId);

        $this->assertCount(2, $missingEnvironments);
        $this->assertInstanceOf(Environment::class, $missingEnvironments->get(1));
        $this->assertInstanceOf(Environment::class, $missingEnvironments->get(2));

        $this->assertEquals(Environment::Testing->value, $missingEnvironments->get(1)->value);
        $this->assertEquals(Environment::Production->value, $missingEnvironments->get(2)->value);
    }


    private function assertClientMatches(array $actual, array $expected): void
    {
        foreach ($actual as $i => $client) {
            $expectedClient = $expected[$i];
            $this->assertInstanceOf(Client::class, $expectedClient);
            $this->assertEquals($expectedClient->clientId, $client->clientId);
            $this->assertEquals($expectedClient->clientSecret, $client->clientSecret);
            $this->assertEquals($expectedClient->environment, $client->environment);
        }
    }
}
