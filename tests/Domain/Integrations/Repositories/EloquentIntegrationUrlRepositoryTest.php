<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Repositories;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\IntegrationUrl;
use App\Domain\Integrations\IntegrationUrlType;
use App\Domain\Integrations\Repositories\EloquentIntegrationUrlRepository;
use Ramsey\Uuid\Uuid;
use Tests\TestCaseWithDatabase;

final class EloquentIntegrationUrlRepositoryTest extends TestCaseWithDatabase
{
    private EloquentIntegrationUrlRepository $integrationUrlRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integrationUrlRepository = new EloquentIntegrationUrlRepository();
    }

    public function test_it_saves_integration_url()
    {
        $integrationUrlId = Uuid::uuid4();
        $integrationId = Uuid::uuid4();

        $integrationUrl = new IntegrationUrl(
            $integrationUrlId,
            $integrationId,
            Environment::Testing,
            IntegrationUrlType::Callback,
            'https://publiqtest.be/callback'
        );

        $this->integrationUrlRepository->save($integrationUrl);

        $this->assertDatabaseHas('integrations_urls', [
            'id' => $integrationUrl->id,
            'integration_id' => $integrationUrl->integrationId,
            'type' => $integrationUrl->type,
            'environment' => $integrationUrl->environment,
            'url' => $integrationUrl->url,
        ]);
    }

    public function test_it_gets_integration_url_by_id()
    {
        $integrationUrlId = Uuid::uuid4();
        $integrationId = Uuid::uuid4();

        $integrationUrl = new IntegrationUrl(
            $integrationUrlId,
            $integrationId,
            Environment::Testing,
            IntegrationUrlType::Callback,
            'https://publiqtest.be/callback'
        );

        $this->integrationUrlRepository->save($integrationUrl);

        $foundIntegration = $this->integrationUrlRepository->getById($integrationUrlId);

        $this->assertEquals($integrationUrl, $foundIntegration);
    }

    public function test_it_gets_integration_urls_by_ids()
    {
        $integrationId = Uuid::uuid4();
        $firstIntegrationUrlId = Uuid::uuid4();
        $secondIntegrationUrlId = Uuid::uuid4();
        $thirdIntegrationUrlId = Uuid::uuid4();

        $firstIntegrationUrl = new IntegrationUrl(
            $firstIntegrationUrlId,
            $integrationId,
            Environment::Testing,
            IntegrationUrlType::Callback,
            'https://publiqtest.be/callback'
        );

        $secondIntegrationUrl = new IntegrationUrl(
            $secondIntegrationUrlId,
            $integrationId,
            Environment::Testing,
            IntegrationUrlType::Callback,
            'https://publiqtest.be/callback'
        );

        $thirdIntegrationUrl = new IntegrationUrl(
            $thirdIntegrationUrlId,
            $integrationId,
            Environment::Testing,
            IntegrationUrlType::Callback,
            'https://publiqtest.be/callback'
        );

        $this->integrationUrlRepository->save($firstIntegrationUrl);
        $this->integrationUrlRepository->save($secondIntegrationUrl);
        $this->integrationUrlRepository->save($thirdIntegrationUrl);

        $foundIntegrationUrls = $this->integrationUrlRepository->getByIds([
            $firstIntegrationUrlId,
            $thirdIntegrationUrlId,
        ]);

        $expected = [
            $firstIntegrationUrl,
            $thirdIntegrationUrl,
        ];

        sort($foundIntegrationUrls);
        sort($expected);

        $this->assertEquals($expected, $foundIntegrationUrls);
    }



    public function testUpdateUrls()
    {

    }

    public function testUpdate()
    {

    }

    public function testDeleteById()
    {

    }
}
