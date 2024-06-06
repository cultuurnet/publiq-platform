<?php

declare(strict_types=1);

namespace Tests\Notifications\Listeners;

use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Notifications\Listeners\NotifyIntegrationChanged;
use App\Notifications\MessageBuilder;
use App\Notifications\Notifier;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class NotifyIntegrationChangedTest extends TestCase
{
    private IntegrationRepository&MockObject $integrationRepository;
    private Notifier&MockObject $notifier;
    private MessageBuilder&MockObject $messageBuilder;
    private LoggerInterface&MockObject $logger;
    private NotifyIntegrationChanged $listener;

    protected function setUp(): void
    {
        parent::setUp();

        $this->integrationRepository = $this->createMock(IntegrationRepository::class);
        $this->notifier = $this->createMock(Notifier::class);
        $this->messageBuilder = $this->createMock(MessageBuilder::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->listener = new NotifyIntegrationChanged(
            $this->integrationRepository,
            $this->notifier,
            $this->messageBuilder,
            $this->logger
        );
    }


    public function test_it_notifies_about_integration_changes(): void
    {
        $integration = new Integration(
            Uuid::uuid4(),
            IntegrationType::SearchApi,
            'Mock Integration',
            'Mock description',
            Uuid::uuid4(),
            IntegrationStatus::Draft,
            IntegrationPartnerStatus::THIRD_PARTY,
        );

        $this->integrationRepository->expects($this->once())
            ->method('getById')
            ->willReturn($integration);

        $this->messageBuilder->expects($this->once())
            ->method('toMessage')
            ->with($integration)
            ->willReturn('Mock message');

        $this->notifier->expects($this->once())
            ->method('postMessage')
            ->with('Mock message');

        $this->listener->handle(new IntegrationCreated($integration->id));
    }
}
