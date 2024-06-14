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

    private const CLIENT_ID_1 = '27c500be-4cc4-4cb2-97d6-c966a27716c4';
    private const CLIENT_ID_2 = 'a1905caa-3bca-4ea0-bd60-aa0a13e844a5';

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
            'id' => self::CLIENT_ID_2,
            'integration_id' => '3c570fb7-ff26-4284-a848-ae8c9c8e205d',
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
            'id' => self::CLIENT_ID_1,
            'integration_id' => '3c570fb7-ff26-4284-a848-ae8c9c8e205d',
            'auth0_client_id' => 'auth0_client1',
            'auth0_client_secret' => 'secret1',
            'auth0_tenant' => 'acc',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getPendingCommand('migrate:keycloak')
            ->expectsConfirmation('Are you sure you want to copy 1 auth0 clients to Keycloak ?', 'yes')
            ->expectsOutput('Converted client 27c500be-4cc4-4cb2-97d6-c966a27716c4 - last updated at ' . now())
            ->assertExitCode(Command::SUCCESS);

        $this->assertDatabaseHas('keycloak_clients', [
            'id' => self::CLIENT_ID_1,
            'integration_id' => '3c570fb7-ff26-4284-a848-ae8c9c8e205d',
            'client_id' => 'auth0_client1',
            'client_secret' => 'secret1',
            'realm' => 'acc',
        ]);
    }

    protected function tearDown(): void
    {
        DB::table('auth0_clients')->where('id', self::CLIENT_ID_1)->delete();
        DB::table('auth0_clients')->where('id', self::CLIENT_ID_2)->delete();
        DB::table('keycloak_clients')->where('id', self::CLIENT_ID_1)->delete();

        parent::tearDown();
    }

    private function getPendingCommand(string $command, array $params = []): PendingCommand
    {
        $command = $this->artisan($command, $params);
        $this->assertInstanceOf(PendingCommand::class, $command);
        return $command;
    }
}
