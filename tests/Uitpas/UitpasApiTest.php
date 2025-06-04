<?php

declare(strict_types=1);

namespace Tests\Uitpas;

use App\Domain\Integrations\Environment;
use App\Keycloak\Client;
use App\Keycloak\Client\HttpClient;
use App\Keycloak\EmptyDefaultScopeConfig;
use App\Keycloak\Realm;
use App\Uitpas\UitpasApi;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;
use Psr\Log\LoggerInterface;

final class UitpasApiTest extends TestCase
{
    private HttpClient&MockObject $client;
    private LoggerInterface&MockObject $logger;
    private UitpasApi $uitpasApi;
    private Realm $realm;

    public function setUp(): void
    {
        parent::setUp();

        $this->realm = new Realm(
            'uitid',
            'uitid',
            'https://test.publiq.be/',
            '123',
            'secret',
            Environment::Testing,
            new EmptyDefaultScopeConfig()
        );
        $this->client = $this->createMock(HttpClient::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->uitpasApi = new UitpasApi(
            $this->client,
            $this->logger,
        );
    }

    public function test_it_adds_permissions_successfully(): void
    {
        $organizerId = 'org-123';
        $clientId = 'client-456';

        $this->client
            ->expects($this->once())
            ->method('sendWithBearer')
            ->willReturn(new Response(204));

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with(sprintf('Gave %s permission to uitpas organisation %s', $organizerId, $clientId));

        $this->uitpasApi->addPermissions($this->realm, $organizerId, $clientId);
    }

    public function test_it_logs_error_when_add_permissions_fails_with_exception(): void
    {
        $organizerId = 'org-123';
        $clientId = 'client-456';

        $this->client
            ->expects($this->once())
            ->method('sendWithBearer')
            ->willThrowException($this->createMock(GuzzleException::class));

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Failed to give'));

        $this->uitpasApi->addPermissions($this->realm, $organizerId, $clientId);
    }

    public function test_it_logs_error_when_status_code_is_not_204(): void
    {
        $organizerId = 'org-123';
        $clientId = 'client-456';

        $this->client
            ->expects($this->once())
            ->method('sendWithBearer')
            ->willReturn(new Response(400));

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with("Failed to give {$organizerId} permission to uitpas organisation {$clientId}, status code 400");

        $this->logger
            ->expects($this->never())
            ->method('info');

        $this->uitpasApi->addPermissions($this->realm, $organizerId, $clientId);
    }

    public function test_it_fetches_permissions_with_the_correct_id(): void
    {
        $client = new Client(Uuid::uuid4(), Uuid::uuid4(), 'client-456', 'client-secret', Environment::Testing);

        $body = json_encode([
            [
                'organizer' => ['id' => 'wrong-id'],
                'permissionDetails' => [
                    ['id' => 'WRONG', 'label' => ['nl' => 'WRONG']],
                ],
            ],
            [
                'organizer' => ['id' => 'org-1'],
                'permissionDetails' => [
                    ['id' => 'TARIFFS_READ', 'label' => ['nl' => 'Tarieven opvragen']],
                    ['id' => 'PASSES_READ', 'label' => ['nl' => 'Basis UiTPAS informatie ophalen']],
                    ['id' => 'TICKETSALES_REGISTER', 'label' => ['nl' => 'Tickets registreren']],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $response = new Response(200, [], $body);

        $this->client
            ->expects($this->once())
            ->method('sendWithBearer')
            ->willReturn($response);

        $permissions = $this->uitpasApi->fetchPermissions($this->realm, $client, 'org-1');

        $this->assertEquals([
            'Basis UiTPAS informatie ophalen',
            'Tarieven opvragen',
            'Tickets registreren',
        ], $permissions);
    }
}
