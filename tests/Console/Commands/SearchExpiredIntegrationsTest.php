<?php

declare(strict_types=1);

namespace Tests\Console\Commands;

use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Events\ActivationExpired;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Mails\MailJet\MailjetConfig;
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

    public function testHandleDispatchesActivationExpired(): void
    {
        $this->mockConfigForEmail(false);

        $integrationId = Uuid::uuid4()->toString();

        DB::table('integrations')->insert([
            'id' => $integrationId,
            'type' => IntegrationType::SearchApi->value,
            'subscription_id' => Uuid::uuid4()->toString(),
            'name' => 'Test',
            'description' => 'test',
            'status' => IntegrationStatus::Draft,
            'created_at' => Carbon::now()->subMonths(8),
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

        $this->getPendingCommand('integration:search-expired-integrations --force')
            ->expectsOutput(sprintf('Dispatched ActivationExpired for integration %s', $integrationId))
            ->assertExitCode(Command::SUCCESS);

        Event::assertDispatched(ActivationExpired::class, static function ($event) use ($integrationId) {
            return $event->id->toString() === $integrationId;
        });
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
