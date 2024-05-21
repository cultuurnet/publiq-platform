<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Keycloak\Listeners\CreateClients;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Ramsey\Uuid\Uuid;

final class KeycloakCreateClient extends Command
{
    protected $signature = 'keycloak:create-client {integrationId : The integration ID}';

    protected $description = 'Create a Keycloak client';

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly CreateClients $createClients,
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

        $this->createClients->handle(new IntegrationCreated($integration->id));

        return self::SUCCESS;
    }
}
