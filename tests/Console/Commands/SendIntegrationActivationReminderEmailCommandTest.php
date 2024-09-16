<?php

declare(strict_types=1);

namespace Tests\Console\Commands;

use App\Mails\MailJet\MailjetConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\PendingCommand;
use Symfony\Component\Console\Command\Command;
use Tests\TestCase;

final class SendIntegrationActivationReminderEmailCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_warns_about_disabled_transactional_mails(): void
    {
        $this->mockConfigForEmail(false);

        $this->getPendingCommand('integration:send-activation-reminder-email --force')
            ->expectsOutput('Email feature flag is disabled - mails not sent')
            ->assertExitCode(Command::FAILURE);
    }

    public function test_it_warns_when_no_integrations_are_found(): void
    {
        $this->mockConfigForEmail(true);

        $this->getPendingCommand('integration:send-activation-reminder-email --force')
            ->expectsOutput('No integrations found to sent reminder emails')
            ->assertExitCode(Command::SUCCESS);
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
