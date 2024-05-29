<?php

declare(strict_types=1);

namespace Tests\Keycloak;

use App\Keycloak\Client;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class ClientTest extends TestCase
{
    use RealmFactory;

    public function test_create_from_json(): void
    {
        $integrationId = Uuid::uuid4();
        $clientId = Uuid::uuid4();
        $data = [
            'id' => Uuid::uuid4()->toString(),
            'secret' => 'testSecret',
        ];

        $client = Client::createFromJson($this->givenTestRealm(), $integrationId, $clientId, $data);

        $this->assertEquals($data['id'], $client->id->toString());
        $this->assertEquals($data['secret'], $client->clientSecret);
        $this->assertEquals($integrationId, $client->integrationId);
        $this->assertEquals($clientId, $client->clientId);
        $this->assertEquals($this->givenTestRealm(), $client->realm);
    }

    public function test_throws_when_missing_secret(): void
    {
        $integrationId = Uuid::uuid4();
        $data = [
            'id' => Uuid::uuid4()->toString(),
            'integrationId' => Uuid::uuid4()->toString(),
        ];

        $this->expectException(InvalidArgumentException::class);

        Client::createFromJson($this->givenTestRealm(), $integrationId, Uuid::uuid4(), $data);
    }
}
