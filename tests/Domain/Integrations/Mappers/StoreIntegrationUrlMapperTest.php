<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Mappers;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\FormRequests\StoreIntegrationUrlRequest;
use App\Domain\Integrations\IntegrationUrl;
use App\Domain\Integrations\IntegrationUrlType;
use App\Domain\Integrations\Mappers\StoreIntegrationUrlMapper;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use Tests\TestCase;
use Tests\UuidTestFactory;

final class StoreIntegrationUrlMapperTest extends TestCase
{
    private const INTEGRATION_URL_ID = 'a8ab2245-17b4-44e3-9920-fab075effbdc';
    private const INTEGRATION_ID = '8549201e-961b-4022-8c37-497f3b599dbe';

    private array $inputs;

    protected function setUp(): void
    {
        parent::setUp();

        Uuid::setFactory(new UuidTestFactory([
            'uuid4' => [
                self::INTEGRATION_URL_ID,
                self::INTEGRATION_ID,
            ]
        ]));

        $this->inputs = [
            'environment' => Environment::Testing->value,
            'type' => IntegrationUrlType::Login->value,
            'url' => 'https://test.testing.com',
        ];
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Uuid::setFactory(new UuidFactory());
    }

    private function getExpectedIntegrationUrl(): IntegrationUrl
    {
        return new IntegrationUrl(
            Uuid::fromString(self::INTEGRATION_URL_ID),
            Uuid::fromString(self::INTEGRATION_ID),
            Environment::from($this->inputs['environment']),
            IntegrationUrlType::from($this->inputs['type']),
            $this->inputs['url']
        );
    }

    public function test_it_creates_an_integration_url_from_request(): void
    {
        $request = new StoreIntegrationUrlRequest();
        $request->merge($this->inputs);

        $expected = $this->getExpectedIntegrationUrl();

        $actual = StoreIntegrationUrlMapper::map($request, self::INTEGRATION_ID);

        $this->assertEquals($expected, $actual);
    }
}
