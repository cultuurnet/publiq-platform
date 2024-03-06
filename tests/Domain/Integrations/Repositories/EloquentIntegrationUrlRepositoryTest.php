<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Repositories;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\IntegrationUrl;
use App\Domain\Integrations\IntegrationUrlType;
use App\Domain\Integrations\Repositories\EloquentIntegrationUrlRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class EloquentIntegrationUrlRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EloquentIntegrationUrlRepository $integrationUrlRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integrationUrlRepository = new EloquentIntegrationUrlRepository();
    }

    public function test_it_saves_integration_url(): void
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

    public function test_it_gets_integration_url_by_id(): void
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

    public function test_it_gets_integration_urls_by_ids(): void
    {
        $integrationId = Uuid::uuid4();
        $firstIntegrationUrlId = Uuid::uuid4();
        $secondIntegrationUrlId = Uuid::uuid4();
        $thirdIntegrationUrlId = Uuid::uuid4();

        $firstIntegrationUrl = new IntegrationUrl(
            $firstIntegrationUrlId,
            $integrationId,
            Environment::Production,
            IntegrationUrlType::Login,
            'https://publiqtest.be/login'
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
            Environment::Production,
            IntegrationUrlType::Logout,
            'https://publiqtest.be/logout'
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



    public function test_it_updates_integration_urls(): void
    {
        $integrationId = Uuid::uuid4();
        $firstIntegrationId = Uuid::uuid4();
        $secondIntegrationId = Uuid::uuid4();
        $thirdIntegrationId = Uuid::uuid4();

        $firstInitialIntegrationUrl = new IntegrationUrl(
            $firstIntegrationId,
            $integrationId,
            Environment::Testing,
            IntegrationUrlType::Callback,
            'https://publiqtest.be/callback-1'
        );

        $secondInitialIntegrationUrl = new IntegrationUrl(
            $secondIntegrationId,
            $integrationId,
            Environment::Production,
            IntegrationUrlType::Login,
            'https://publiqtest.be/login'
        );

        $thirdInitialIntegrationUrl = new IntegrationUrl(
            $thirdIntegrationId,
            $integrationId,
            Environment::Acceptance,
            IntegrationUrlType::Logout,
            'https://publiqtest.be/logout'
        );

        $this->integrationUrlRepository->save($firstInitialIntegrationUrl);
        $this->integrationUrlRepository->save($secondInitialIntegrationUrl);
        $this->integrationUrlRepository->save($thirdInitialIntegrationUrl);

        $firstUpdatedIntegrationUrl = new IntegrationUrl(
            $firstIntegrationId,
            $integrationId,
            Environment::Testing,
            IntegrationUrlType::Callback,
            'https://publiqtest.be/callback-new'
        );
        $thirdUpdatedIntegrationUrl = new IntegrationUrl(
            $thirdIntegrationId,
            $integrationId,
            Environment::Acceptance,
            IntegrationUrlType::Logout,
            'https://publiqtest.be/logout-new'
        );

        $this->integrationUrlRepository->updateUrls(collect([
            $firstUpdatedIntegrationUrl,
            $thirdUpdatedIntegrationUrl,
        ]));

        $expected = [
            $firstUpdatedIntegrationUrl,
            $secondInitialIntegrationUrl,
            $thirdUpdatedIntegrationUrl,
        ];

        $foundIntegrationUrls = $this->integrationUrlRepository->getByIds([
            $firstIntegrationId,
            $secondIntegrationId,
            $thirdIntegrationId,
        ]);

        sort($expected);
        sort($foundIntegrationUrls);

        $this->assertEquals($expected, $foundIntegrationUrls);
    }

    public function test_it_updates_integration_url(): void
    {
        $integrationUrlId = Uuid::uuid4();
        $integrationId = Uuid::uuid4();

        $initialIntegrationUrl = new IntegrationUrl(
            $integrationUrlId,
            $integrationId,
            Environment::Testing,
            IntegrationUrlType::Callback,
            'https://publiqtest.be/callback'
        );

        $this->integrationUrlRepository->save($initialIntegrationUrl);

        $updatedIntegrationUrl = new IntegrationUrl(
            $integrationUrlId,
            $integrationId,
            Environment::Testing,
            IntegrationUrlType::Callback,
            'https://publiqtest.be/callback-new'
        );

        $this->integrationUrlRepository->update($updatedIntegrationUrl);

        $foundIntegrationUrl = $this->integrationUrlRepository->getById($integrationUrlId);

        $this->assertEquals($updatedIntegrationUrl, $foundIntegrationUrl);
    }

    public function test_it_deletes_by_id(): void
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

        $this->integrationUrlRepository->deleteById($integrationUrl->id);

        $this->expectException(ModelNotFoundException::class);
        $this->integrationUrlRepository->getById($integrationUrl->id);
    }
}
