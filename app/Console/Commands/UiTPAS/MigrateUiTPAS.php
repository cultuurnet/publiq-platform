<?php

declare(strict_types=1);

namespace App\Console\Commands\UiTPAS;

use App\Auth0\Auth0Client;
use App\Auth0\Auth0ClusterSDK;
use App\Auth0\Auth0Tenant;
use App\Auth0\Repositories\Auth0ClientRepository;
use App\Console\Commands\ReadCsvFile;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationPartnerStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\KeyVisibility;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Subscriptions\Repositories\SubscriptionRepository;
use App\Domain\Subscriptions\SubscriptionCategory;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class MigrateUiTPAS extends Command
{
    use ReadCsvFile;

    protected $signature = 'uitpas:migrate';

    protected $description = 'Migrate UiTPAS data from CSV file (database/uitpas/integrations.csv) to database';

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly ContactRepository $contactRepository,
        private readonly Auth0ClusterSDK $auth0ClusterSDK,
        private readonly Auth0ClientRepository $auth0ClientRepository
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        Model::unsetEventDispatcher();

        $rows = $this->readCsvFile('database/uitpas/integrations.csv');
        $uitpasIntegrations = array_map(
            fn (array $row) => new UiTPASIntegration($row),
            $rows
        );

        $integrationsCount = count($uitpasIntegrations);
        if ($integrationsCount <= 0) {
            $this->warn('No UiTPAS integrations to import');
            return self::SUCCESS;
        }

        if (!$this->confirm('Are you sure you want to import ' . $integrationsCount . ' UiTPAS integrations?')) {
            return self::SUCCESS;
        }

        foreach ($uitpasIntegrations as $uitpasIntegration) {
            $integrationId = Uuid::uuid4();

            $this->info($integrationId . ' - Started importing project ' . $uitpasIntegration->name());

            $this->migrateIntegration($integrationId, $uitpasIntegration);

            $this->migrateContacts($integrationId, $uitpasIntegration);

            $this->migrateAuth0Clients($integrationId, $uitpasIntegration);

            $this->info($integrationId . ' - Ended importing project ' . $uitpasIntegration->name());
            $this->info('---');
        }

        return self::SUCCESS;
    }

    private function migrateIntegration(UuidInterface $integrationId, UiTPASIntegration $uiTPASIntegration): void
    {
        $subscriptionId = $this->subscriptionRepository->getByIntegrationTypeAndCategory(
            IntegrationType::UiTPAS,
            SubscriptionCategory::Free
        )->id;

        $integration = (new Integration(
            $integrationId,
            IntegrationType::UiTPAS,
            $uiTPASIntegration->name(),
            $uiTPASIntegration->description(),
            $subscriptionId,
            $uiTPASIntegration->status(),
            IntegrationPartnerStatus::THIRD_PARTY,
        ))
            ->withKeyVisibility(KeyVisibility::v2)
            ->withWebsite($uiTPASIntegration->website());

        $this->integrationRepository->save($integration);

        IntegrationModel::query()->where('id', '=', $integrationId)->update([
            'migrated_at' => Carbon::now(),
        ]);
    }

    private function migrateContacts(UuidInterface $integrationId, UiTPASIntegration $uitpasIntegration): void
    {
        $contacts = $uitpasIntegration->contacts($integrationId);

        foreach ($contacts as $contact) {
            $this->contactRepository->save($contact);
        }
    }

    private function migrateAuth0Clients(UuidInterface $integrationId, UiTPASIntegration $uitpasIntegration): void
    {
        $auth0Tenants = [Auth0Tenant::Testing, Auth0Tenant::Production];

        foreach ($auth0Tenants as $auth0Tenant) {
            $clientId = $uitpasIntegration->clientIdForTenant($auth0Tenant);
            if (empty($clientId)) {
                $this->warn('No client ID for ' . $auth0Tenant->value . ' tenant');
                continue;
            }

            $auth0TenantSDK = $this->auth0ClusterSDK->getTenantSDK($auth0Tenant);

            $testCredentials = $auth0TenantSDK->getClientSecret($clientId);

            $auth0Client = new Auth0Client(
                Uuid::uuid4(),
                $integrationId,
                $clientId,
                $testCredentials,
                $auth0Tenant
            );

            $this->auth0ClientRepository->save($auth0Client);
        }
    }
}
