<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Mappers;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\FormRequests\StoreIntegrationUrlRequest;
use App\Domain\Integrations\IntegrationUrl;
use App\Domain\Integrations\IntegrationUrlType;
use App\Domain\Integrations\Mappers\StoreIntegrationUrlMapper;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\UuidTestFactory;

final class StoreIntegrationUrlMapperTest extends TestCase
{
    private array $inputs;
    private array $ids;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ids = [
            'a8ab2245-17b4-44e3-9920-fab075effbdc', // integrationUrlId
            '8549201e-961b-4022-8c37-497f3b599dbe', // integrationId
        ];

        Uuid::setFactory(new UuidTestFactory([
            'uuid4' => $this->ids,
        ]));

        $this->inputs = [
            'environment' => Environment::Testing->value,
            'type' => IntegrationUrlType::Login->value,
            'url' => 'https://test.testing.com',
        ];
    }

    private function getExpectedIntegrationUrl(): IntegrationUrl
    {
        return new IntegrationUrl(
            Uuid::fromString($this->ids[0]),
            Uuid::fromString($this->ids[1]),
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

        $actual = StoreIntegrationUrlMapper::map($request, $this->ids[1]);

        $this->assertEquals($expected, $actual);
    }
}
