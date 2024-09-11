<?php

declare(strict_types=1);

namespace Tests\Console\Commands;

use App\Console\Commands\SendIntegrationActivationReminderEmail;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Mails\MailJet\MailjetConfig;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\PendingCommand;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class SendIntegrationActivationReminderEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_feature_flag_is_off(): void
    {
        $this->mockConfigForEmail(false);

        $this->getPendingCommand('cronjob:send-activation-reminder-email --force')
            ->expectsOutput('Email feature flag is disabled - mails not sent')
            ->assertExitCode(SendIntegrationActivationReminderEmail::FAILURE);
    }

    public function test_no_integrations_found(): void
    {
        $this->mockConfigForEmail(true);

        $this->getPendingCommand('cronjob:send-activation-reminder-email --force')
            ->expectsOutput('No integrations found to sent reminder emails')
            ->assertExitCode(SendIntegrationActivationReminderEmail::SUCCESS);
    }

    public function test_confirmation_mail_sent(): void
    {
        $this->mockConfigForEmail(true);

        $integrationId = Uuid::uuid4()->toString();
        DB::table('integrations')->insert([
            'id' => $integrationId,
            'type' => IntegrationType::SearchApi->value,
            'subscription_id' => Uuid::uuid4()->toString(),
            'name' => 'Test',
            'description' => 'test',
            'status' => IntegrationStatus::Draft,
            'created_at' => Carbon::now()->subYears(2),
            'sent_reminder_email' => null,
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

        $this->getPendingCommand('cronjob:send-activation-reminder-email --force')
            ->expectsOutput(sprintf('Sending activation reminder about integration %s to bril.smurf@example.com, grote.smurf@example.com', $integrationId))
            ->assertExitCode(SendIntegrationActivationReminderEmail::SUCCESS);

        $this->assertDatabaseHas('integrations', [
            'id' => $integrationId,
            'sent_reminder_email' => $now,
        ]);
    }

    private function mockConfigForEmail(bool $enabled): void
    {
        config()->set(MailjetConfig::TRANSACTIONAL_EMAILS_ENABLED, $enabled);
    }

    private function getPendingCommand(string $command, array $params = []): PendingCommand
    {
        $command = $this->artisan($command, $params);
        $this->assertInstanceOf(PendingCommand::class, $command);
        return $command;
    }
}
