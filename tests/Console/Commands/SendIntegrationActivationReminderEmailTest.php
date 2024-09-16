<?php

declare(strict_types=1);

namespace Tests\Console\Commands;

use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\EloquentIntegrationRepository;
use App\Domain\Integrations\Repositories\EloquentUdbOrganizerRepository;
use App\Domain\Mail\Mailer;
use App\Domain\Mail\MailManager;
use App\Domain\Subscriptions\Repositories\EloquentSubscriptionRepository;
use App\Mails\SendIntegrationActivationReminderEmail;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class SendIntegrationActivationReminderEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmation_mail_sent(): void
    {
        $integrationId = Uuid::uuid4()->toString();
        DB::table('integrations')->insert([
            'id' => $integrationId,
            'type' => IntegrationType::SearchApi->value,
            'subscription_id' => Uuid::uuid4()->toString(),
            'name' => 'Test',
            'description' => 'test',
            'status' => IntegrationStatus::Draft,
            'created_at' => Carbon::now()->subYears(2),
            'reminder_email_sent' => null,
        ]);
        DB::table('contacts')->insert([
            'id' => Uuid::uuid4()->toString(),
            'integration_id' => $integrationId,
            'email' => 'grote.smurf@example.com',
            'type' => ContactType::Technical->value,
            'first_name' => 'Grote',
            'last_name' => 'Smurf',
        ]);
        DB::table('contacts')->insert([
            'id' => Uuid::uuid4()->toString(),
            'integration_id' => $integrationId,
            'email' => 'bril.smurf@example.com',
            'type' => ContactType::Functional->value,
            'first_name' => 'Bril',
            'last_name' => 'Smurf',
        ]);

        $now = Carbon::now();
        Carbon::setTestNow($now);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('info')
            ->with(sprintf('Sending activation reminder about integration %s to bril.smurf@example.com, grote.smurf@example.com', $integrationId));

        $mailer = $this->createMock(Mailer::class);
        $mailer->expects($this->exactly(2))
            ->method('send');

        $integrationRepository = new EloquentIntegrationRepository(
            new EloquentUdbOrganizerRepository(),
            new EloquentSubscriptionRepository(),
        );

        $mailManager = new MailManager(
            $mailer,
            $integrationRepository,
            1,
            2,
            3,
            4,
            'http://www.example.com'
        );

        $service = new SendIntegrationActivationReminderEmail(
            $mailManager,
            $integrationRepository,
            $logger
        );

        $service->send($integrationRepository->getDraftsByTypeAndOlderThenMonthsAgo(IntegrationType::SearchApi, 12));

        $this->assertDatabaseHas('integrations', [
            'id' => $integrationId,
            'reminder_email_sent' => $now,
        ]);
    }
}
