<?php

declare(strict_types=1);

namespace App\Auth0\Listeners;

use App\Auth0\Auth0Client;
use App\Auth0\Auth0Tenant;
use App\Auth0\Repositories\Auth0ClientRepository;
use App\Domain\Integrations\Events\IntegrationActivatedWithCoupon;
use App\Domain\Integrations\Events\IntegrationActivatedWithOrganization;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Ramsey\Uuid\UuidInterface;

final class DistributeClients implements ShouldQueue
{
    use Queueable;

    public function __construct(
        readonly private IntegrationRepository $integrationRepository,
        readonly private Auth0ClientRepository $auth0ClientRepository
    ) {
    }

    public function handleIntegrationActivatedWithCoupon(IntegrationActivatedWithCoupon $integrationActivatedWithCoupon): void
    {
        $this->distributeClientsForIntegration($integrationActivatedWithCoupon->id);
    }

    public function handleIntegrationActivatedWithOrganization(IntegrationActivatedWithOrganization $integrationActivatedWithOrganization): void
    {
        $this->distributeClientsForIntegration($integrationActivatedWithOrganization->id);
    }

    private function distributeClientsForIntegration(UuidInterface $integrationId): void
    {
        $integration = $this->integrationRepository->getById($integrationId);
        $clients = array_filter($integration->auth0Clients(), fn (Auth0Client $client) => $client->tenant === Auth0Tenant::Production);

        $this->auth0ClientRepository->distribute(...$clients);
    }
}
