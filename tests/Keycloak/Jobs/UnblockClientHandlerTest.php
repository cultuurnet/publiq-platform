<?php

declare(strict_types=1);

namespace Tests\Keycloak\Jobs;

use App\Domain\Integrations\Environment;
use App\Keycloak\Client;
use App\Keycloak\Client\ApiClient;
use App\Keycloak\Events\ClientUnblocked;
use App\Keycloak\Jobs\UnblockClient;
use App\Keycloak\Jobs\UnblockClientHandler;
use App\Keycloak\Repositories\KeycloakClientRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;
use Tests\Keycloak\KeycloakHttpClientFactory;
use Tests\Keycloak\RealmFactory;

final class UnblockClientHandlerTest extends TestCase
{
    use KeycloakHttpClientFactory;

    use RealmFactory;

    public function test_unblock_client_handler(): void
    {
        Event::fake();

        $client = new Client(
            Uuid::uuid4(),
            Uuid::uuid4(),
            Uuid::uuid4()->toString(),
            'client-secret-1',
            Environment::Acceptance
        );

        $keycloakClientRepository = $this->createMock(KeycloakClientRepository::class);
        $keycloakClientRepository->expects($this->once())
            ->method('getById')
            ->with($client->id)
            ->willReturn($client);

        $apiClient = $this->createMock(ApiClient::class);
        $apiClient->expects($this->once())
            ->method('unblockClient')
            ->with($client);

        $handler = new UnblockClientHandler(
            $apiClient,
            $keycloakClientRepository,
            new NullLogger()
        );

        $handler->handle(new UnblockClient($client->id));

        Event::assertDispatched(ClientUnblocked::class);
    }

    public function test_handler_fails_when_client_does_not_exists(): void
    {
        $client = new Client(
            Uuid::uuid4(),
            Uuid::uuid4(),
            Uuid::uuid4()->toString(),
            'client-secret-1',
            Environment::Acceptance
        );

        $keycloakClientRepository = $this->createMock(KeycloakClientRepository::class);
        $keycloakClientRepository->expects($this->once())
            ->method('getById')
            ->with($client->id)
            ->willThrowException(new ModelNotFoundException('Client does not exist'));

        $apiClient = $this->createMock(ApiClient::class);
        $apiClient->expects($this->never())
            ->method('unblockClient');

        $handler = new UnblockClientHandler(
            $apiClient,
            $keycloakClientRepository,
            new NullLogger()
        );

        $handler->handle(new UnblockClient($client->id));
    }
}
