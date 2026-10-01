<?php

declare(strict_types=1);

namespace Tests\Notifications\Slack;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Domain\Subscriptions\Repositories\SubscriptionRepository;
use App\Domain\UdbUuid;
use App\Keycloak\Client;
use App\Notifications\Slack\SlackMessageBuilder;
use App\Search\Sapi3\SearchService;
use App\Search\UdbOrganizerNameResolver;
use CultuurNet\SearchV3\ValueObjects\PagedCollection;
use PHPUnit\Framework\MockObject\MockObject;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Tests\CreateIntegration;
use Tests\TestCase;

final class SlackMessageBuilderTest extends TestCase
{
    use CreateIntegration;

    private SearchService&MockObject $searchService;
    private SlackMessageBuilder $messageBuilder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->searchService = $this->createMock(SearchService::class);
        $this->searchService->method('findOrganizers')->willReturn(new PagedCollection());

        $this->messageBuilder = new SlackMessageBuilder(
            $this->createMock(SubscriptionRepository::class),
            new UdbOrganizerNameResolver(),
            $this->searchService,
            'https://uitpas.test/clients/',
            'https://udb.test/',
            'https://platform.test',
        );
    }

    public function test_it_links_to_uitpas_with_the_production_client_id(): void
    {
        $integrationId = Uuid::uuid4();

        $integration = $this->givenThereIsAnIntegration($integrationId, ['type' => IntegrationType::UiTPAS])
            ->withKeycloakClients(
                new Client(Uuid::uuid4(), $integrationId, 'client-test', 'secret', Environment::Testing),
                new Client(Uuid::uuid4(), $integrationId, 'client-prod', 'secret', Environment::Production),
            );

        $message = $this->messageBuilder->toMessageWithOrganizer(
            $integration,
            $this->givenThereIsAnOrganizer($integrationId)
        );

        $this->assertStringContainsString('• *Open in UiTPAS:* https://uitpas.test/clients/client-prod', $message);
    }

    public function test_it_still_builds_a_message_without_a_production_client(): void
    {
        $integrationId = Uuid::uuid4();

        $integration = $this->givenThereIsAnIntegration($integrationId, ['type' => IntegrationType::UiTPAS])
            ->withKeycloakClients(
                new Client(Uuid::uuid4(), $integrationId, 'client-test', 'secret', Environment::Testing),
            );

        $message = $this->messageBuilder->toMessageWithOrganizer(
            $integration,
            $this->givenThereIsAnOrganizer($integrationId)
        );

        $this->assertStringContainsString('• *Open in UiTPAS:* N/A', $message);
        $this->assertStringContainsString('• *Open in publiq-platform:* https://platform.test', $message);
    }

    private function givenThereIsAnOrganizer(UuidInterface $integrationId): UdbOrganizer
    {
        return new UdbOrganizer(
            Uuid::uuid4(),
            $integrationId,
            new UdbUuid(Uuid::uuid4()->toString()),
            UdbOrganizerStatus::Pending,
            null
        );
    }
}
