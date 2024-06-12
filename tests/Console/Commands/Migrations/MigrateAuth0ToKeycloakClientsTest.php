<?php

declare(strict_types=1);

namespace Tests\Console\Commands\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\PendingCommand;
use Symfony\Component\Console\Command\Command;
use Tests\TestCase;

final class MigrateAuth0ToKeycloakClientsTest extends TestCase
{
    use RefreshDatabase;

    public function test_invalid_date_format(): void
    {
        $this->getPendingCommand('migrate:keycloak', ['updated_at' => 'invalid-date'])
            ->expectsOutput('Invalid format used for updated at, use the format YYYY-MM-DD')
            ->assertExitCode(Command::FAILURE);
    }

    public function test_no_clients_to_migrate(): void
    {
        $this->getPendingCommand('migrate:keycloak')
            ->expectsOutput('No clients found to migrate ')
            ->assertExitCode(Command::FAILURE);
    }

    public function test_no_clients_to_migrate_with_date(): void
    {
        DB::table('auth0_clients')->insert([
            'id' => 'client2',
            'integration_id' => 'integration1',
            'auth0_client_id' => 'auth0_client1',
            'auth0_client_secret' => 'secret1',
            'auth0_tenant' => 'tenant1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // This will be updated at the start of the next century
        $this->getPendingCommand('migrate:keycloak', ['updated_at' => '2100-01-01'])
            ->expectsOutput('No clients found to migrate starting from 2100-01-01')
            ->assertExitCode(Command::FAILURE);
    }

    public function test_migration_with_clients(): void
    {
        DB::table('auth0_clients')->insert([
            'id' => 'client1',
            'integration_id' => 'integration1',
            'auth0_client_id' => 'auth0_client1',
            'auth0_client_secret' => 'secret1',
            'auth0_tenant' => 'tenant1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getPendingCommand('migrate:keycloak')
            ->expectsConfirmation('Are you sure you want to copy 1 auth0 clients to Keycloak ?', 'yes')
            ->expectsOutput('Converted client client1 - last updated at ' . now())
            ->assertExitCode(Command::SUCCESS);

        $this->assertDatabaseHas('keycloak_clients', [
            'id' => 'client1',
            'integration_id' => 'integration1',
            'client_id' => 'auth0_client1',
            'client_secret' => 'secret1',
            'realm' => 'tenant1',
        ]);
    }

    protected function tearDown(): void
    {
        DB::table('auth0_clients')->where('id', 'client1')->delete();
        DB::table('auth0_clients')->where('id', 'client2')->delete();
        DB::table('keycloak_clients')->where('id', 'client1')->delete();

        parent::tearDown();
    }

    private function getPendingCommand(string $command, array $params = []): PendingCommand
    {
        $command = $this->artisan($command, $params);
        $this->assertInstanceOf(PendingCommand::class, $command);
        return $command;
    }
}
