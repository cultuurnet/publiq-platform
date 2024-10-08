<?php

declare(strict_types=1);

namespace App\Console\Commands\Migrations;

use App\Domain\Integrations\Environment;
use App\Keycloak\Client;
use App\Keycloak\Exception\RealmNotAvailable;
use App\Keycloak\Repositories\KeycloakClientRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

final class MigrateAuth0ToKeycloakClients extends Command
{
    protected $signature = 'migrate:keycloak';
    protected $description = 'Copy all auth0 clients to keycloak clients - does NOT remove the auth0 clients.';

    public function __construct(private readonly KeycloakClientRepository $keycloakClientRepository)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $auth0Clients = $this->findAuth0Clients();

        $total = count($auth0Clients);
        if ($total <= 0) {
            $this->warn('No clients found to migrate');
            return self::SUCCESS;
        }

        if (!$this->confirm(
            sprintf(
                'Are you sure you want to copy %s auth0 clients to Keycloak?',
                $total
            )
        )) {
            return self::FAILURE;
        }

        $bar = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($auth0Clients as $auth0Client) {
            $client = new Client(
                Uuid::fromString($auth0Client->id),
                Uuid::fromString($auth0Client->integration_id),
                $auth0Client->auth0_client_id,
                $auth0Client->auth0_client_secret,
                Environment::from($auth0Client->auth0_tenant),
            );

            try {
                $this->keycloakClientRepository->create($client);

                $this->info(sprintf('Converted client %s', $auth0Client->id));
            } catch (RealmNotAvailable $e) {
                $this->error('Failed to sync ' . $auth0Client->id . ' - ' . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();

        return self::SUCCESS;
    }

    private function findAuth0Clients(): Collection
    {
        $query = DB::table('auth0_clients')
            ->orderBy('updated_at', 'asc')
            ->whereNotIn('auth0_clients.id', function ($query) {
                $query->select('id')
                    ->from('keycloak_clients');
            })
            ->whereNull('deleted_at');

        return $query->get();
    }
}
