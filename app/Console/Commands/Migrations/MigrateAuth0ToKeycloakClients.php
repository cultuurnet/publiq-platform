<?php

declare(strict_types=1);

namespace App\Console\Commands\Migrations;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class MigrateAuth0ToKeycloakClients extends Command
{
    protected $signature = 'migrate:keycloak {updated_at?}';//updated_at format =
    protected $description = 'Copy all auth0 clients to keycloak clients - does NOT remove the auth0 clients.';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $updatedAt = $this->argument('updated_at');
        $auth0Clients = $this->findAuth0Clients($updatedAt);

        if ($updatedAt !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $updatedAt)) {
            $this->warn('Invalid format used for updated at, use the format YYYY-MM-DD');
            return self::FAILURE;
        }

        $total = count($auth0Clients);
        if ($total <= 0) {
            $this->warn($updatedAt ? 'No clients found to migrate starting from ' . $updatedAt : 'No clients found to migrate ');
            return self::FAILURE;
        }

        if (!$this->confirm(
            sprintf(
                'Are you sure you want to copy %s auth0 clients to Keycloak %s?',
                $total,
                $updatedAt ? 'starting from ' . $updatedAt : ''
            )
        )) {
            return self::FAILURE;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($auth0Clients as $client) {
            DB::table('keycloak_clients')->insert([
                'id' => $client->id,
                'integration_id' => $client->integration_id,
                'client_id' => $client->auth0_client_id,
                'client_secret' => $client->auth0_client_secret,
                'realm' => $client->auth0_tenant,
                'created_at' => $client->created_at,
                'updated_at' => $client->updated_at,
                'deleted_at' => $client->deleted_at,
            ]);

            $this->info(sprintf('Converted client %s - last updated at %s', $client->id, $client->updated_at));

            $bar->advance();
        }

        $bar->finish();

        return self::SUCCESS;
    }

    private function findAuth0Clients(?string $updatedAt): Collection
    {
        $query = DB::table('auth0_clients')
            ->orderBy('updated_at', 'asc')
            ->whereNotIn('auth0_clients.id', function ($query) {
                $query->select('id')
                    ->from('keycloak_clients');
            })
            ->whereNull('deleted_at');

        if ($updatedAt !== null) {
            $query->where('updated_at', '>', $updatedAt);
        }

        return $query->get();
    }
}
