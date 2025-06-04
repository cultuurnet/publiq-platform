<?php

declare(strict_types=1);

namespace Tests\Keycloak;

use App\Domain\Integrations\Environment;
use App\Keycloak\Realm;
use App\Keycloak\Realms;
use App\Keycloak\DefaultScopeConfig;
use Illuminate\Support\Facades\Config;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class RealmsTest extends TestCase
{
    public function testBuildCreatesRealmsCollection(): void
    {
        $scopes = [
            Environment::Acceptance->value => [
                'search_api_id' => Uuid::uuid4()->toString(),
                'entry_api_id' => Uuid::uuid4()->toString(),
                'uitpas_id' => Uuid::uuid4()->toString(),
            ],
            Environment::Testing->value => [
                'search_api_id' => Uuid::uuid4()->toString(),
                'entry_api_id' => Uuid::uuid4()->toString(),
                'uitpas_id' => Uuid::uuid4()->toString(),
            ],
            Environment::Production->value => [
                'search_api_id' => Uuid::uuid4()->toString(),
                'entry_api_id' => Uuid::uuid4()->toString(),
                'uitpas_id' => Uuid::uuid4()->toString(),
            ],
        ];

        Config::set('keycloak.environments', [
            Environment::Acceptance->value => [
                'internalName' => 'internal_name_0',
                'base_url' => 'https://publiq-platform.be/0',
                'client_id' => 'client_id_0',
                'client_secret' => 'client_secret_0',
                'scope' => $scopes[Environment::Acceptance->value],
            ],
            Environment::Testing->value => [
                'internalName' => 'internal_name_1',
                'base_url' => 'https://publiq-platform.be/1',
                'client_id' => 'client_id_1',
                'client_secret' => 'client_secret_1',
                'scope' => $scopes[Environment::Testing->value],
            ],
            Environment::Production->value => [
                'internalName' => 'internal_name_2',
                'base_url' => 'https://publiq-platform.be/2',
                'client_id' => 'client_id_2',
                'client_secret' => 'client_secret_2',
                'scope' => $scopes[Environment::Production->value],
            ],
        ]);

        $realms = Realms::build();

        $this->assertCount(3, $realms);

        foreach (Environment::cases() as $i => $environment) {
            $realm = $realms->get($i);

            $this->assertInstanceOf(Realm::class, $realm);
            $this->assertEquals('internal_name_' . $i, $realm->internalName);
            $this->assertEquals(ucfirst($environment->value), $realm->publicName);
            $this->assertEquals('https://publiq-platform.be/' . $i . '/', $realm->baseUrl);  // This tests if a trailing slash is added
            $this->assertEquals('client_id_' . $i, $realm->clientId);
            $this->assertEquals('client_secret_' . $i, $realm->clientSecret);
            $this->assertEquals($environment, $realm->environment);

            $this->assertInstanceOf(DefaultScopeConfig::class, $realm->scopeConfig);
            $this->assertEquals($scopes[$environment->value]['search_api_id'], $realm->scopeConfig->searchApiScopeId->toString());
            $this->assertEquals($scopes[$environment->value]['entry_api_id'], $realm->scopeConfig->entryApiScopeId->toString());
            $this->assertEquals($scopes[$environment->value]['uitpas_id'], $realm->scopeConfig->uitpasScopeId->toString());
        }
    }

    public function testSkipEmptyEnvironments(): void
    {
        Config::set('keycloak.environments', [
            Environment::Acceptance->value => [
                'base_url' => '',
            ],
        ]);

        $realms = Realms::build();

        $this->assertCount(0, $realms);
    }
}
