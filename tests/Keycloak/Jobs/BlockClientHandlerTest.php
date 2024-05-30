<?php

declare(strict_types=1);

namespace Tests\Keycloak\Jobs;

use App\Keycloak\Client;
use App\Keycloak\Client\ApiClient;
use App\Keycloak\Events\ClientBlocked;
use App\Keycloak\Jobs\BlockClient;
use App\Keycloak\Jobs\BlockClientHandler;
use App\Keycloak\Repositories\KeycloakClientRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;
use Tests\Keycloak\RealmFactory;

final class BlockClientHandlerTest extends TestCase
{
    use RealmFactory;

    public function test_block_client_handler(): void
    {
        Event::fake();

        $this->assertTrue(true);

        $client = new Client(
            Uuid::uuid4(),
            Uuid::uuid4(),
            Uuid::uuid4(),
            'client-secret-1',
            $this->givenAcceptanceRealm()
        );

        $keycloakClientRepository = $this->createMock(KeycloakClientRepository::class);
        $keycloakClientRepository->expects($this->once())
            ->method('getById')
            ->with($client->id)
            ->willReturn($client);

        $apiClient = $this->createMock(ApiClient::class);
        $apiClient->expects($this->once())
            ->method('blockClient')
            ->with($client);

        $handler = new BlockClientHandler(
            $apiClient,
            $keycloakClientRepository,
            new NullLogger()
        );

        $handler->handle(new BlockClient($client->id));

        Event::assertDispatched(ClientBlocked::class);
    }

    public function test_handler_fails_when_client_does_not_exists(): void
    {
        $client = new Client(
            Uuid::uuid4(),
            Uuid::uuid4(),
            Uuid::uuid4(),
            'client-secret-1',
            $this->givenAcceptanceRealm()
        );

        $keycloakClientRepository = $this->createMock(KeycloakClientRepository::class);
        $keycloakClientRepository->expects($this->once())
            ->method('getById')
            ->with($client->id)
            ->willThrowException(new ModelNotFoundException('Client does not exist'));

        $apiClient = $this->createMock(ApiClient::class);
        $apiClient->expects($this->never())
            ->method('blockClient');

        $handler = new BlockClientHandler(
            $apiClient,
            $keycloakClientRepository,
            new NullLogger()
        );

        $handler->handle(new BlockClient($client->id));
    }
}
