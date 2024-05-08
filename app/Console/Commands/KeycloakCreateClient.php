<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Keycloak\Config;
use App\Keycloak\ScopeConfig;
use App\Keycloak\Service\ApiClient;
use App\Keycloak\Service\CreateClientFlow;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

final class KeycloakCreateClient extends Command
{
    protected $signature = 'keycloak:create-client {integrationId : The integration ID}';

    protected $description = 'Create a Keycloak client';

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly ApiClient $apiClient,
        private readonly Config $config,
        private readonly ScopeConfig $scopeConfig,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $integrationId = $this->argument('integrationId');
        try {
            $integration = $this->integrationRepository->getById(Uuid::fromString($integrationId));
        } catch (ModelNotFoundException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $flow = new CreateClientFlow(
            $this->apiClient,
            $this->config,
            $this->scopeConfig,
            $this->logger
        );

        $clients = $flow->createClientsForIntegration($integration);

        foreach ($clients as $client) {
            $this->info(sprintf("Created Keycloak client for realm '%s' with client ID: %s", $client->realm->internalName, $client->clientId));
        }

        return self::SUCCESS;
    }
}
