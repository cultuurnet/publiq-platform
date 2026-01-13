<?php

declare(strict_types=1);

namespace Tests\Console\Commands;

use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Events\ActivationExpired;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Mails\Template\TemplateName;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\PendingCommand;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Tests\TestCase;

final class SearchExpiredIntegrationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_warns_when_no_integrations_are_found(): void
    {
        $this->getPendingCommand('integration:search-expired-integrations --force')
            ->expectsOutput('No expired integrations')
            ->assertExitCode(Command::SUCCESS);
    }

    public function test_it_listens_to_the_option_first_reminder(): void
    {
        $integrationId = Uuid::uuid4()->toString();
        $this->createIntegrationWithContacts($integrationId, 14);

        $this->getPendingCommand('integration:search-expired-integrations --force --only-first-reminder')
            ->expectsOutput('No expired integrations')
            ->assertExitCode(Command::SUCCESS);
    }

    public function test_it_listens_to_the_option_final_reminder(): void
    {
        $integrationId = Uuid::uuid4()->toString();
        $this->createIntegrationWithContacts($integrationId, 8);

        $this->getPendingCommand('integration:search-expired-integrations --force --only-final-reminder')
            ->expectsOutput('No expired integrations')
            ->assertExitCode(Command::SUCCESS);
    }

    public function test_can_handle_search_expired_integrations(): void
    {
        $integrationId = Uuid::uuid4()->toString();
        $integrationId2 = Uuid::uuid4()->toString();

        $this->createIntegrationWithContacts($integrationId, 8);
        $this->createIntegrationWithContacts($integrationId2, 14);

        $this->getPendingCommand('integration:search-expired-integrations --force')
            ->expectsOutput(sprintf('Dispatched ActivationExpired for integration %s', $integrationId))
            ->expectsOutput(sprintf('Dispatched ActivationExpired for integration %s', $integrationId2))
            ->assertExitCode(Command::SUCCESS);

        Event::assertDispatched(ActivationExpired::class, static function (ActivationExpired $event) use ($integrationId, $integrationId2) {
            return match ($event->templateName) {
                TemplateName::INTEGRATION_ACTIVATION_REMINDER => $event->id->toString() === $integrationId,
                TemplateName::INTEGRATION_FINAL_ACTIVATION_REMINDER => $event->id->toString() === $integrationId2,
                default => false,
            };
        });
    }

    public function test_it_does_not_send_mails_to_integrations_on_hold(): void
    {
        $integrationId = Uuid::uuid4()->toString();
        $integrationIdOnHold = Uuid::uuid4()->toString();

        $this->createIntegrationWithContacts($integrationId, 8);
        $this->createIntegrationWithContacts($integrationIdOnHold, 8, onHold: true);

        $this->getPendingCommand('integration:search-expired-integrations --force')
            ->expectsOutput(sprintf('Dispatched ActivationExpired for integration %s', $integrationId))
            ->assertExitCode(Command::SUCCESS);

        Event::assertDispatched(ActivationExpired::class, static function (ActivationExpired $event) use ($integrationId, $integrationIdOnHold) {
            return $event->id->toString() === $integrationId && $event->id->toString() !== $integrationIdOnHold;
        });
    }

    private function getPendingCommand(string $command, array $params = []): PendingCommand
    {
        $command = $this->artisan($command, $params);
        $this->assertInstanceOf(PendingCommand::class, $command);
        return $command;
    }

    private function createIntegrationWithContacts(string $integrationId, int $monthsAgo, bool $onHold = false): void
    {
        DB::table('integrations')->insert([
            'id' => $integrationId,
            'type' => IntegrationType::SearchApi->value,
            'subscription_id' => Uuid::uuid4()->toString(),
            'name' => 'Test',
            'description' => 'test',
            'status' => IntegrationStatus::Draft,
            'created_at' => Carbon::now()->subMonths($monthsAgo),
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

        if ($onHold) {
            DB::table('admin_information')->insert([
                'id' => Uuid::uuid4()->toString(),
                'integration_id' => $integrationId,
                'on_hold' => true,
            ]);
        }
    }
}
