<?php

declare(strict_types=1);

namespace Tests\Uitpas;

use App\Domain\Integrations\Environment;
use App\Keycloak\Client\HttpClient;
use App\Keycloak\EmptyDefaultScopeConfig;
use App\Keycloak\Realm;
use App\Uitpas\UitpasApi;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Tests\TestCase;

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
}
