<?php

declare(strict_types=1);

namespace App\Console\Commands\Migrations;

use App\Domain\Auth\Models\UserModel;
use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Insightly\InsightlyMapping;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Spatie\Activitylog\Facades\CauserResolver;

final class FixContacts extends Command
{
    use ReadCsvFile;

    protected $signature = 'migrate:fix-contacts';

    protected $description = 'Fix the missing technical and or functional contacts in the database.';

    public function __construct(
        private readonly ContactRepository $contactRepository,
        private readonly InsightlyMappingRepository $insightlyMappingRepository
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        Model::unsetEventDispatcher();

        CauserResolver::setCauser(UserModel::createSystemUser());

        $this->info('Fixing contacts...');

        // @phpstan-ignore-next-line
        $integrationsIds = IntegrationModel::all()->pluck('id')->toArray();
        $integrationsIdsCount = count($integrationsIds);

        if ($integrationsIdsCount <= 0) {
            $this->warn('No integrations to fix contacts for');
            return 0;
        }

        if (!$this->confirm('Are you sure you want to fix ' . $integrationsIdsCount . ' integrations?')) {
            return 0;
        }

        foreach ($integrationsIds as $integrationId) {
            $this->info(PHP_EOL . '--- Start: Fixing contacts for integration ' . $integrationId);

            $contacts = $this->contactRepository->getByIntegrationId(Uuid::fromString($integrationId));

            $contributor = $contacts->first(fn (Contact $contact) => $contact->type === ContactType::Contributor);
            $technical = $contacts->first(fn (Contact $contact) => $contact->type === ContactType::Technical);
            $functional = $contacts->first(fn (Contact $contact) => $contact->type === ContactType::Functional);

            if ($contributor === null) {
                $this->warn('No contributor found for integration ' . $integrationId);
                continue;
            }

            if ($technical !== null && $functional !== null) {
                $this->info('Technical and functional contacts already exist for integration ' . $integrationId);
                continue;
            }

            $this->info('Contributor found with id ' . $contributor->id . ' and email ' . $contributor->email);

            try {
                $contributorMapping = $this->insightlyMappingRepository->getByIdAndType(
                    $contributor->id,
                    ResourceType::Contact
                );
            } catch (ModelNotFoundException) {
                $contributorMapping = null;
                $this->warn('No contributor mapping found for integration ' . $integrationId);
            }

            if ($technical === null) {
                $this->saveContact(
                    Uuid::fromString($integrationId),
                    $contributor,
                    $contributorMapping,
                    ContactType::Technical
                );
            }

            if ($functional === null) {
                $this->saveContact(
                    Uuid::fromString($integrationId),
                    $contributor,
                    $contributorMapping,
                    ContactType::Functional
                );
            }
        }

        return 0;
    }

    private function saveContact(
        UuidInterface $integrationId,
        Contact $contributor,
        ?InsightlyMapping $contributorMapping,
        ContactType $contactType
    ): void {
        $this->info('Fixing ' . $contactType->value . ' contact for integration' . $integrationId);

        $functionalId = Uuid::uuid4();
        $this->contactRepository->save(
            new Contact(
                $functionalId,
                $integrationId,
                $contributor->email,
                $contactType,
                $contributor->firstName,
                $contributor->lastName
            )
        );

        if ($contributorMapping !== null) {
            $this->insightlyMappingRepository->save(
                new InsightlyMapping(
                    $functionalId,
                    $contributorMapping->insightlyId,
                    ResourceType::Contact,
                )
            );
        }
    }
}
