<?php

declare(strict_types=1);

namespace Tests\UiTPAS;

use App\Api\ClientCredentialsContext;
use App\Api\TokenStrategy\ClientCredentials;
use App\Domain\Integrations\Environment;
use App\Domain\UdbUuid;
use App\Json;
use App\UiTPAS\Dto\UiTPASPermission;
use App\UiTPAS\Dto\UiTPASPermissionDetail;
use App\UiTPAS\Dto\UiTPASPermissionDetails;
use App\UiTPAS\UiTPASApi;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
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
    private const ORG_ID = 'd541dbd6-b818-432d-b2be-d51dfc5c0c51';
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
            new Response(200, [], json_encode([], JSON_THROW_ON_ERROR)),
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

        $uitpasApi->updatePermissions($this->context, new UdbUuid(self::ORG_ID), self::CLIENT_ID);
    }

    public function test_it_adds_permissions_successfully_and_keeps_the_current_permissions(): void
    {
        $currentPermissions = [
            [
                'organizer' => ['id' => 'f668a72f-a35a-4758-ac62-948f1302eae5', 'name' => 'the other organizer'],
                'permissionDetails' => [
                    ['id' => 'TARIFFS_READ', 'label' => ['nl' => 'Tarieven opvragen']],
                    ['id' => 'PASSES_READ', 'label' => ['nl' => 'Basis UiTPAS informatie ophalen']],
                    ['id' => 'TICKETSALES_REGISTER', 'label' => ['nl' => 'Tickets registreren']],
                ],
            ],
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => self::MY_TOKEN], JSON_THROW_ON_ERROR)),
            new Response(200, [], json_encode($currentPermissions, JSON_THROW_ON_ERROR)),
            new Response(204),
        ]);

        $history = [];
        $historyMiddleware = Middleware::history($history);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push($historyMiddleware);

        $client = new Client(['handler' => $handlerStack]);
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

        $uitpasApi->updatePermissions($this->context, new UdbUuid(self::ORG_ID), self::CLIENT_ID);

        $this->assertIsArray($history);
        $this->assertCount(3, $history); // token fetch, get current permissions, put updated permissions

        $this->assertJson((string)$history[2]['request']->getBody());
        $decodedBody = json_decode((string)$history[2]['request']->getBody(), true, 512, JSON_THROW_ON_ERROR);

        //checking of new and old permissions exists
        $this->assertCount(2, $decodedBody);
        $this->assertSame(self::ORG_ID, $decodedBody[1]['organizer']['id']);
        $this->assertEquals('CHECKINS_WRITE', $decodedBody[1]['permissionDetails'][0]['id']);
    }

    public function test_it_logs_error_when_add_permissions_fails_with_exception(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => self::MY_TOKEN], JSON_THROW_ON_ERROR)),
            new Response(200, [], json_encode([], JSON_THROW_ON_ERROR)),
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

        $uitpasApi->updatePermissions($this->context, new UdbUuid(self::ORG_ID), self::CLIENT_ID);
    }

    public function test_it_logs_error_when_status_code_is_not_204(): void
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => self::MY_TOKEN], JSON_THROW_ON_ERROR)),
            new Response(200, [], json_encode([], JSON_THROW_ON_ERROR)),
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

        $uitpasApi->updatePermissions($this->context, new UdbUuid(self::ORG_ID), self::CLIENT_ID);
    }

    public function test_it_fetches_permissions_with_the_correct_id(): void
    {
        $body = json_encode([
            [
                'organizer' => ['id' => '33f1722b-04fc-4652-b99f-2c96de87cf82', 'name' => 'wrong'],
                'permissionDetails' => [
                    ['id' => 'WRONG', 'label' => ['nl' => 'WRONG']],
                ],
            ],
            [
                'organizer' => ['id' => self::ORG_ID, 'name' => 'correct'],
                'permissionDetails' => [
                    ['id' => 'TARIFFS_READ', 'label' => ['nl' => 'Tarieven opvragen']],
                    ['id' => 'PASSES_READ', 'label' => ['nl' => 'Basis UiTPAS informatie ophalen']],
                    ['id' => 'TICKETSALES_REGISTER', 'label' => ['nl' => 'Tickets registreren']],
                ],
            ],
        ], JSON_THROW_ON_ERROR);

        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => self::MY_TOKEN], JSON_THROW_ON_ERROR)),
            new Response(200, [], $body),
        ]);

        $client = $this->givenClient($mock);
        $uitpasApi = new UiTPASApi(
            $client,
            new ClientCredentials($client, $this->logger),
            $this->logger,
            'https://test-uitpas.publiq.be/',
            'https://uitpas.publiq.be/',
        );

        $permissions = $uitpasApi->fetchPermissions(
            $this->context,
            new UdbUuid(self::ORG_ID),
            'client-id'
        );

        $expectedPermissions = new UiTPASPermission(
            new UdbUuid(self::ORG_ID),
            'correct',
            new UiTPASPermissionDetails([
                new UiTPASPermissionDetail('TARIFFS_READ', 'Tarieven opvragen'),
                new UiTPASPermissionDetail('PASSES_READ', 'Basis UiTPAS informatie ophalen'),
                new UiTPASPermissionDetail('TICKETSALES_REGISTER', 'Tickets registreren'),
            ])
        );

        $this->assertEquals($expectedPermissions, $permissions);
    }

    public function test_it_returns_empty_error_when_permissions_api_fails(): void
    {
        $this->logger->expects($this->once())
            ->method('error')
            ->with('Failed to fetch permissions: does not exist');

        $mock = new MockHandler([
            new Response(200, [], json_encode(['access_token' => self::MY_TOKEN], JSON_THROW_ON_ERROR)),
            new Response(404, [], 'does not exist'),
        ]);

        $client = $this->givenClient($mock);
        $uitpasApi = new UiTPASApi(
            $client,
            new ClientCredentials($client, $this->logger),
            $this->logger,
            'https://test-uitpas.publiq.be/',
            'https://uitpas.publiq.be/',
        );

        $permission = $uitpasApi->fetchPermissions(
            $this->context,
            new UdbUuid(self::ORG_ID),
            'client-id'
        );

        $this->assertNull($permission);
    }

    public function test_delete_all_permissions_successfully(): void
    {
        $originalPermissions = [
            ['organizer' => ['id' => self::ORG_ID]],
            ['organizer' => ['id' => 'another-id']],
        ];

        $expectedPermissions = [
            ['organizer' => ['id' => 'another-id']],
        ];

        $mockHandler = new MockHandler([
            new Response(200, [], json_encode(['access_token' => self::MY_TOKEN], JSON_THROW_ON_ERROR)),
            new Response(200, [], Json::encode($originalPermissions)), // GET permissions
            new Response(204), // PUT updated permissions
        ]);

        $container = [];
        $history = Middleware::history($container);

        $stack = HandlerStack::create($mockHandler);
        $stack->push($history);

        $client = new Client(['handler' => $stack]);

        $uitpasApi = new UiTPASApi(
            $client,
            new ClientCredentials($client, $this->logger),
            $this->logger,
            'https://test-uitpas.publiq.be/',
            'https://uitpas.publiq.be/',
        );

        $result = $uitpasApi->deleteAllPermissions(
            $this->context,
            new UdbUuid(self::ORG_ID),
            self::CLIENT_ID
        );

        $this->assertTrue($result);

        /** @var Request $putRequest */
        $putRequest = $container[2]['request'];
        $this->assertEquals('PUT', $putRequest->getMethod());
        $putBody = Json::decodeAssociatively($putRequest->getBody()->getContents());
        $this->assertEquals($expectedPermissions, $putBody);
    }
}
