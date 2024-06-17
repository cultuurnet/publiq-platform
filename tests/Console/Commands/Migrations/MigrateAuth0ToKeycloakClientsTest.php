<?php

declare(strict_types=1);

namespace Tests\Console\Commands\Migrations;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\PendingCommand;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Command\Command;
use Tests\TestCase;

final class MigrateAuth0ToKeycloakClientsTest extends TestCase
{
    use RefreshDatabase;

    private const CLIENT_ID_1 = '27c500be-4cc4-4cb2-97d6-c966a27716c4';
    private const CLIENT_ID_2 = 'a1905caa-3bca-4ea0-bd60-aa0a13e844a5';

    public function test_no_clients_to_migrate(): void
    {
        $this->getPendingCommand('migrate:keycloak')
            ->expectsOutput('No clients found to migrate')
            ->assertExitCode(Command::SUCCESS);
    }


    public function test_complex_query_clients_already_exist_in_keycloak_table(): void
    {
        $integrationId = Uuid::uuid4()->toString();
        $id1 = Uuid::uuid4()->toString();
        $id2 = Uuid::uuid4()->toString();

        DB::table('auth0_clients')->insert([
            'id' => $id1,
            'integration_id' => $integrationId,
            'auth0_client_id' => 'deleted_client',
            'auth0_client_secret' => 'secret1',
            'auth0_tenant' => 'acc',
            'created_at' => now(),
            'updated_at' => now(),
            'deleted_at' => now(),
        ]);

        DB::table('auth0_clients')->insert([
            'id' => $id2,
            'integration_id' => $integrationId,
            'auth0_client_id' => 'client_already_exist',
            'auth0_client_secret' => 'secret1',
            'auth0_tenant' => 'test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('keycloak_clients')->insert([
            'id' => $id2,
            'integration_id' => $integrationId,
            'client_id' => 'client_already_exist',
            'client_secret' => 'secret1',
            'realm' => 'test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->getPendingCommand('migrate:keycloak')
            ->expectsOutput('No clients found to migrate')
            ->assertExitCode(Command::SUCCESS);

        $this->assertDatabaseCount('auth0_clients', 2);
        $this->assertDatabaseCount('keycloak_clients', 1);
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
            ->expectsConfirmation('Are you sure you want to copy 1 auth0 clients to Keycloak?', 'yes')
            ->expectsOutput('Converted client 27c500be-4cc4-4cb2-97d6-c966a27716c4')
            ->assertExitCode(Command::SUCCESS);

        $this->assertDatabaseHas('keycloak_clients', [
            'id' => self::CLIENT_ID_1,
            'integration_id' => '3c570fb7-ff26-4284-a848-ae8c9c8e205d',
            'client_id' => 'auth0_client1',
            'client_secret' => 'secret1',
            'realm' => 'acc',
        ]);
    }

    private function getPendingCommand(string $command, array $params = []): PendingCommand
    {
        $command = $this->artisan($command, $params);
        $this->assertInstanceOf(PendingCommand::class, $command);
        return $command;
    }
}
