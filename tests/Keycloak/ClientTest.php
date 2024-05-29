<?php

declare(strict_types=1);

namespace Tests\Keycloak;

use App\Keycloak\Client;
use App\Keycloak\Realm;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use App\Domain\Integrations\Environment;

final class ClientTest extends TestCase
{
    public function test_create_from_json(): void
    {
        $realm = new Realm('uitidpoc', 'acceptance', Environment::Acceptance);
        $integrationId = Uuid::uuid4();
        $clientId = Uuid::uuid4();
        $data = [
            'id' => Uuid::uuid4()->toString(),
            'secret' => 'testSecret',
        ];

        $client = Client::createFromJson($realm, $integrationId, $clientId, $data);

        $this->assertEquals($data['id'], $client->id->toString());
        $this->assertEquals($data['secret'], $client->clientSecret);
        $this->assertEquals($integrationId, $client->integrationId);
        $this->assertEquals($clientId, $client->clientId);
        $this->assertEquals($realm, $client->realm);
    }

    public function test_throws_when_missing_secret(): void
    {
        $realm = new Realm('uitidpoc', 'acceptance', Environment::Acceptance);
        $integrationId = Uuid::uuid4();
        $data = [
            'id' => Uuid::uuid4()->toString(),
            'integrationId' => Uuid::uuid4()->toString(),
        ];

        $this->expectException(InvalidArgumentException::class);

        Client::createFromJson($realm, $integrationId, Uuid::uuid4(), $data);
    }
}
