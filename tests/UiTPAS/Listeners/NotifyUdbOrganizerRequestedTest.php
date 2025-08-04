<?php

declare(strict_types=1);

namespace Tests\UiTPAS\Listeners;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Events\IntegrationActivationRequested;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Integrations\UdbOrganizer;
use App\Domain\Integrations\UdbOrganizerStatus;
use App\Domain\UdbUuid;
use App\Keycloak\Client;
use App\Notifications\MessageBuilder;
use App\Notifications\Notifier;
use App\UiTPAS\Event\UdbOrganizerRequested;
use App\UiTPAS\Listeners\NotifyUdbOrganizerRequested;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use Tests\CreateIntegration;
use Tests\TestCase;

final class NotifyUdbOrganizerRequestedTest extends TestCase
{
    use CreateIntegration;

    private IntegrationRepository&MockObject $integrationRepo;
    private Notifier&MockObject $notifier;
    private MessageBuilder&MockObject $messageBuilder;
    private LoggerInterface&MockObject $logger;
    private NotifyUdbOrganizerRequested $listener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integrationRepo = $this->createMock(IntegrationRepository::class);
        $this->notifier = $this->createMock(Notifier::class);
        $this->messageBuilder = $this->createMock(MessageBuilder::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->listener = new NotifyUdbOrganizerRequested(
            $this->integrationRepo,
            $this->notifier,
            $this->messageBuilder,
            $this->logger
        );
    }

    public function test_it_sends_a_slack_message_for_uitpas_integration(): void
    {
        $integrationId = Uuid::uuid4();

        $event = new IntegrationActivationRequested($integrationId);

        $org = new UdbOrganizer(
            Uuid::uuid4(),
            $integrationId,
            new UdbUuid(Uuid::uuid4()->toString()),
            UdbOrganizerStatus::Pending
        );

        $integration = $this->givenThereIsAnIntegration($integrationId, ['type' => IntegrationType::UiTPAS])
            ->withKeycloakClients(
                new Client(Uuid::uuid4(), Uuid::uuid4(), 'client-id-wrong', 'secret', Environment::Testing),
                new Client(Uuid::uuid4(), Uuid::uuid4(), 'client-123', 'secret', Environment::Production),
            )
            ->withUdbOrganizers(
                $org
            );

        $this->integrationRepo->expects($this->once())
            ->method('getById')
            ->with($org->integrationId)
            ->willReturn($integration);

        $this->notifier->expects($this->once())
            ->method('postMessage');

        $this->messageBuilder->expects($this->once())
            ->method('toMessageWithOrganizer')
            ->with($integration, $org);

        $this->listener->handleIntegrationActivationRequested($event);
    }

    public function test_it_does_not_send_message_for_non_uitpas_integration(): void
    {
        $integrationId = Uuid::uuid4();

        $event = new IntegrationActivationRequested($integrationId);

        $org = new UdbOrganizer(
            Uuid::uuid4(),
            $integrationId,
            new UdbUuid(Uuid::uuid4()->toString()),
            UdbOrganizerStatus::Pending
        );

        $integration = $this->givenThereIsAnIntegration($integrationId, ['type' => IntegrationType::SearchApi]);

        $this->integrationRepo->expects($this->once())
            ->method('getById')
            ->with($org->integrationId)
            ->willReturn($integration);

        $this->notifier->expects($this->never())
            ->method('postMessage');

        $this->messageBuilder->expects($this->never())
            ->method('toMessageWithOrganizer');

        $this->listener->handleIntegrationActivationRequested($event);
    }

    public function test_it_logs_when_failed(): void
    {
        $integrationId = Uuid::uuid4();
        $event = new IntegrationActivationRequested($integrationId);
        $exception = new RuntimeException('Something went wrong');

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                'Failed to notify about requested udb organizers',
                $this->callback(function ($context) use ($integrationId, $exception) {
                    return $context['integration_id'] === $integrationId->toString()
                        && $context['exception'] === $exception;
                })
            );

        $this->listener->failed($event, $exception);
    }

    public function test_it_does_not_message_approved_organizers(): void
    {
        $integrationId = Uuid::uuid4();

        $event = new IntegrationActivationRequested($integrationId);

        $org = new UdbOrganizer(
            Uuid::uuid4(),
            $integrationId,
            new UdbUuid(Uuid::uuid4()->toString()),
            UdbOrganizerStatus::Approved
        );

        $integration = $this->givenThereIsAnIntegration($integrationId, ['type' => IntegrationType::SearchApi]);

        $this->integrationRepo->expects($this->once())
            ->method('getById')
            ->with($org->integrationId)
            ->willReturn($integration);

        $this->notifier->expects($this->never())
            ->method('postMessage');

        $this->messageBuilder->expects($this->never())
            ->method('toMessageWithOrganizer');

        $this->listener->handleIntegrationActivationRequested($event);
    }

    public function test_it_sends_mail_when_udb_organizer_is_requested(): void
    {
        $event = new UdbOrganizerRequested(
            $udbId = new UdbUuid(Uuid::uuid4()->toString()),
            $integrationId = Uuid::uuid4(),
        );
        $org = new UdbOrganizer(
            Uuid::uuid4(),
            $integrationId,
            $udbId,
            UdbOrganizerStatus::Pending
        );

        $integration = $this->givenThereIsAnIntegration($integrationId, ['type' => IntegrationType::UiTPAS])
            ->withKeycloakClients(
                new Client(Uuid::uuid4(), Uuid::uuid4(), 'client-id-wrong', 'secret', Environment::Testing),
                new Client(Uuid::uuid4(), Uuid::uuid4(), 'client-123', 'secret', Environment::Production),
            )
            ->withUdbOrganizers(
                $org
            );

        $this->integrationRepo->expects($this->once())
            ->method('getById')
            ->with($org->integrationId)
            ->willReturn($integration);

        $this->notifier->expects($this->once())
            ->method('postMessage');

        $this->messageBuilder->expects($this->once())
            ->method('toMessageWithOrganizer')
            ->with($integration, $org);

        $this->listener->handleUdbOrganizerRequested($event);
    }
}
