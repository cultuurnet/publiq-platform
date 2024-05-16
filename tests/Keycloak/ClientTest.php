<?php

declare(strict_types=1);

namespace Tests\Keycloak;

use App\Keycloak\Client;
use App\Keycloak\Realm;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class ClientTest extends TestCase
{
    public function test_create_from_json(): void
    {
        $realm = new Realm('uitidpoc', 'acceptance');
        $integrationId = Uuid::uuid4();
        $data = [
            'id' => Uuid::uuid4()->toString(),
            'secret' => 'testSecret',
        ];

        $client = Client::createFromJson($realm, $integrationId, $data);

        $this->assertEquals($data['id'], $client->id->toString());
        $this->assertEquals($data['secret'], $client->clientSecret);
        $this->assertEquals($integrationId, $client->integrationId);
        $this->assertEquals($realm, $client->realm);
    }

    public function test_throws_when_missing_secret(): void
    {
        $realm = new Realm('uitidpoc', 'acceptance');
        $integrationId = Uuid::uuid4();
        $data = [
            'id' => Uuid::uuid4()->toString(),
            'integrationId' => Uuid::uuid4()->toString(),
        ];

        $this->expectException(InvalidArgumentException::class);

        Client::createFromJson($realm, $integrationId, $data);
    }
}
