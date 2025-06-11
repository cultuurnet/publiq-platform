<?php

declare(strict_types=1);

namespace Tests\Uitpas;

use App\Api\ClientCredentialsContext;
use App\Api\TokenStrategy\ClientCredentials;
use App\Domain\Integrations\Environment;
use App\UiTPAS\UiTPASApi;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Tests\Keycloak\KeycloakHttpClientFactory;
use Tests\TestCase;

final class UitpasApiTest extends TestCase
{
    use KeycloakHttpClientFactory;

    private const MY_TOKEN = 'my-token';
    private const ORG_ID = 'org-123';
    private const CLIENT_ID = 'client-456';

    private LoggerInterface&MockObject $logger;
    private ClientCredentialsContext $context;

    public function setUp(): void
    {
        parent::setUp();

        $this->context = new ClientCredentialsContext(
            Environment::Testing,
            'https://test.publiq.be/',
            '123',
            'secret',
            'uitid'
        );

        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function test_it_adds_permissions_successfully(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => self::MY_TOKEN], JSON_THROW_ON_ERROR)),
            new Response(204),
        ]);

        $client = $this->givenClient($mock);
        $uitpasApi = new UiTPASApi(
            $client,
            new ClientCredentials($client, $this->logger),
            $this->logger,
            'https://test-uitpas.publiq.be/',
            'https://uitpas.publiq.be/',
        );

        $callCount = 0;
        $this->logger
            ->expects($this->exactly(2))
            ->method('info')
            ->with($this->callback(function (string $message) use (&$callCount) {
                if ($callCount === 0) {
                    $expected = 'Fetched token for 123, token starts with my-tok';
                } else {
                    $expected = sprintf('Gave %s permission to uitpas organisation %s', self::CLIENT_ID, self::ORG_ID);
                }

                $callCount++;
                return $message === $expected;
            }));

        $uitpasApi->addPermissions($this->context, self::ORG_ID, self::CLIENT_ID);
    }

    public function test_it_logs_error_when_add_permissions_fails_with_exception(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => self::MY_TOKEN], JSON_THROW_ON_ERROR)),
            new RequestException(
                'Ja lap, het is kapot',
                new Request('PUT', 'https://test-uitpas.publiq.be/permissions/' . self::CLIENT_ID)
            ),
        ]);

        $client = $this->givenClient($mock);
        $uitpasApi = new UiTPASApi(
            $client,
            new ClientCredentials($client, $this->logger),
            $this->logger,
            'https://test-uitpas.publiq.be/',
            'https://uitpas.publiq.be/',
        );

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with($this->stringContains('Failed to give'));

        $uitpasApi->addPermissions($this->context, self::ORG_ID, self::CLIENT_ID);
    }

    public function test_it_logs_error_when_status_code_is_not_204(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => self::MY_TOKEN], JSON_THROW_ON_ERROR)),
            new Response(400),
        ]);

        $client = $this->givenClient($mock);
        $uitpasApi = new UiTPASApi(
            $client,
            new ClientCredentials($client, $this->logger),
            $this->logger,
            'https://test-uitpas.publiq.be/',
            'https://uitpas.publiq.be/',
        );

        $this->logger
            ->expects($this->once())
            ->method('error')
            ->with(sprintf('Failed to give %s permission to uitpas organisation %s, status code 400', self::CLIENT_ID, self::ORG_ID));

        $uitpasApi->addPermissions($this->context, self::ORG_ID, self::CLIENT_ID);
    }
}
