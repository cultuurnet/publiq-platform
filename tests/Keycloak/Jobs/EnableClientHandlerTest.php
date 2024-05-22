<?php

declare(strict_types=1);

namespace Tests\Keycloak\Jobs;

use App\Keycloak\Client;
use App\Keycloak\Events\ClientEnabled;
use App\Keycloak\Jobs\EnableClient;
use App\Keycloak\Jobs\EnableClientHandler;
use App\Keycloak\Realm;
use App\Keycloak\Repositories\KeycloakClientRepository;
use App\Keycloak\Service\ApiClient;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Ramsey\Uuid\Uuid;
use Tests\Keycloak\KeycloakHelper;

final class EnableClientHandlerTest extends TestCase
{
    use KeycloakHelper;

    public function test_enable_client_handler(): void
    {
        Event::fake();

        $client = new Client(
            Uuid::uuid4(),
            Uuid::uuid4(),
            'client-secret-1',
            Realm::getMasterRealm()
        );

        $keycloakClientRepository = $this->createMock(KeycloakClientRepository::class);
        $keycloakClientRepository->expects($this->once())
            ->method('getById')
            ->with($client->id)
            ->willReturn($client);

        $apiClient = $this->createMock(ApiClient::class);
        $apiClient->expects($this->once())
            ->method('enableClient')
            ->with($client);

        $handler = new EnableClientHandler(
            $apiClient,
            $keycloakClientRepository,
            new NullLogger()
        );

        $handler->handle(new EnableClient($client->id));

        Event::assertDispatched(ClientEnabled::class);
    }

    public function test_handler_fails_when_client_does_not_exists(): void
    {
        $client = new Client(
            Uuid::uuid4(),
            Uuid::uuid4(),
            'client-secret-1',
            Realm::getMasterRealm()
        );

        $keycloakClientRepository = $this->createMock(KeycloakClientRepository::class);
        $keycloakClientRepository->expects($this->once())
            ->method('getById')
            ->with($client->id)
            ->willThrowException(new ModelNotFoundException('Client does not exist'));

        $apiClient = $this->createMock(ApiClient::class);
        $apiClient->expects($this->never())
            ->method('enableClient');

        $handler = new EnableClientHandler(
            $apiClient,
            $keycloakClientRepository,
            new NullLogger()
        );

        $handler->handle(new EnableClient($client->id));
    }
}
