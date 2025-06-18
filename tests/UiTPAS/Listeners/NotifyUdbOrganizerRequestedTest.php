<?php

declare(strict_types=1);

namespace Tests\UiTPAS\Listeners;

use App\Domain\Integrations\Environment;
use App\Domain\Integrations\Events\UdbOrganizerCreated;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Integrations\Repositories\UdbOrganizerRepository;
use App\Domain\Integrations\UdbOrganizer;
use App\Keycloak\Client;
use App\Notifications\MessageBuilder;
use App\Notifications\Notifier;
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

    private UdbOrganizerRepository&MockObject $orgRepo;
    private IntegrationRepository&MockObject $integrationRepo;
    private Notifier&MockObject $notifier;
    private MessageBuilder&MockObject $messageBuilder;
    private LoggerInterface&MockObject $logger;
    private NotifyUdbOrganizerRequested $listener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orgRepo = $this->createMock(UdbOrganizerRepository::class);
        $this->integrationRepo = $this->createMock(IntegrationRepository::class);
        $this->notifier = $this->createMock(Notifier::class);
        $this->messageBuilder = $this->createMock(MessageBuilder::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->listener = new NotifyUdbOrganizerRequested(
            $this->orgRepo,
            $this->integrationRepo,
            $this->notifier,
            $this->messageBuilder,
            $this->logger
        );
    }

    public function test_it_sends_a_slack_message_for_uitpas_integration(): void
    {
        $uuid = Uuid::uuid4();

        $event = new UdbOrganizerCreated($uuid);

        $org = new UdbOrganizer(
            Uuid::uuid4(),
            Uuid::uuid4(),
            'org-1234'
        );

        $integration = $this->givenThereIsAnIntegration($uuid, ['type' => IntegrationType::UiTPAS])
            ->withKeycloakClients(
                new Client(Uuid::uuid4(), Uuid::uuid4(), 'client-id-wrong', 'secret', Environment::Testing),
                new Client(Uuid::uuid4(), Uuid::uuid4(), 'client-123', 'secret', Environment::Production),
            );

        $this->orgRepo->expects($this->once())
            ->method('getById')
            ->with($uuid)
            ->willReturn($org);

        $this->integrationRepo->expects($this->once())
            ->method('getById')
            ->with($org->integrationId)
            ->willReturn($integration);

        $this->notifier->expects($this->once())
            ->method('postMessage');

        $this->messageBuilder->expects($this->once())
            ->method('toMessageWithOrganizer')
            ->with($integration, $org);

        $this->listener->handle($event);
    }

    public function test_it_does_not_send_message_for_non_uitpas_integration(): void
    {
        $uuid = Uuid::uuid4();

        $event = new UdbOrganizerCreated($uuid);

        $org = new UdbOrganizer(
            Uuid::uuid4(),
            Uuid::uuid4(),
            'org-1234'
        );

        $integration = $this->givenThereIsAnIntegration($uuid, ['type' => IntegrationType::SearchApi]);

        $this->orgRepo->expects($this->once())
            ->method('getById')
            ->with($uuid)
            ->willReturn($org);

        $this->integrationRepo->expects($this->once())
            ->method('getById')
            ->with($org->integrationId)
            ->willReturn($integration);

        $this->notifier->expects($this->never())
            ->method('postMessage');

        $this->messageBuilder->expects($this->never())
            ->method('toMessageWithOrganizer');

        $this->listener->handle($event);
    }

    public function test_it_logs_when_failed(): void
    {
        $uuid = Uuid::uuid4();
        $event = new UdbOrganizerCreated($uuid);
        $exception = new RuntimeException('Something went wrong');

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                'Failed to notify about requested udb organizer',
                $this->callback(function ($context) use ($uuid, $exception) {
                    return $context['org_id'] === $uuid->toString()
                        && $context['exception'] === $exception;
                })
            );

        $this->listener->failed($event, $exception);
    }
}
