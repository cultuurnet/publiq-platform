<?php

declare(strict_types=1);

namespace App\Console\Commands\UiTPAS;

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
            return 0;
        }

        if (!$this->confirm('Are you sure you want to import ' . $integrationsCount . ' UiTPAS integrations?')) {
            return 0;
        }

        foreach ($uitpasIntegrations as $uitpasIntegration) {
            $integrationId = Uuid::uuid4();

            $this->info($integrationId . ' - Started importing project ' . $uitpasIntegration->name());

            $this->migrateIntegration($integrationId, $uitpasIntegration);

            $this->migrateContacts($integrationId, $uitpasIntegration);

            $this->info($integrationId . ' - Ended importing project ' . $uitpasIntegration->name());
            $this->info('---');
        }

        return 0;
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
}
