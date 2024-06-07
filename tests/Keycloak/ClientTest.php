<?php

declare(strict_types=1);

namespace Tests\Keycloak;

use App\Keycloak\Client;
use InvalidArgumentException;
use Tests\TestCase;
use Ramsey\Uuid\Uuid;

final class ClientTest extends TestCase
{
    use RealmFactory;

    public function test_create_from_json(): void
    {
        $integrationId = Uuid::uuid4();
        $data = [
            'id' => Uuid::uuid4()->toString(),
            'secret' => 'testSecret',
            'clientId' => Uuid::uuid4()->toString(),
        ];

        $client = Client::createFromJson($this->givenTestRealm(), $integrationId, $data);

        $this->assertEquals($data['id'], $client->id->toString());
        $this->assertEquals($data['secret'], $client->clientSecret);
        $this->assertEquals($data['clientId'], $client->clientId);
        $this->assertEquals($integrationId, $client->integrationId);
        $this->assertEquals($this->givenTestRealm()->environment, $client->environment);
    }

    public function test_throws_when_missing_secret(): void
    {
        $integrationId = Uuid::uuid4();
        $data = [
            'id' => Uuid::uuid4()->toString(),
            'integrationId' => Uuid::uuid4()->toString(),
        ];

        $this->expectException(InvalidArgumentException::class);

        Client::createFromJson($this->givenTestRealm(), $integrationId, $data);
    }
}
