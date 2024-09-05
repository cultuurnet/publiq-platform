<?php

declare(strict_types=1);

namespace App\Console\Commands\Migrations;

use App\Console\Commands\ReadCsvFile;
use App\Domain\Auth\Models\UserModel;
use App\Domain\Contacts\Contact;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Insightly\InsightlyMapping;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\UniqueConstraintViolationException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Spatie\Activitylog\Facades\CauserResolver;

final class MigrateContacts extends Command
{
    use ReadCsvFile;

    protected $signature = 'migrate:contacts';

    protected $description = 'Migrate contacts provided in the contacts.csv file (database/project-aanvraag/contacts.csv)';

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

        $this->info('Migrating contacts...');

        $contacts = $this->readCsvFile('database/project-aanvraag/contacts.csv');
        $contactsCount = count($contacts);

        if ($contactsCount <= 0) {
            $this->warn('No contacts to import');
            return 0;
        }

        if (!$this->confirm('Are you sure you want to import ' . $contactsCount . ' contacts?')) {
            return 0;
        }

        $migrationContacts = array_map(fn (array $contact) => new ContactCsvRow($contact), $contacts);
        foreach ($migrationContacts as $migrationContact) {
            $contactId = Uuid::uuid4();

            $this->info($contactId . ' - Started importing contact ' . $migrationContact->email());

            try {
                $this->migrateContact($contactId, $migrationContact);
            } catch (UniqueConstraintViolationException) {
                $this->warn($contactId . ' - Contact ' . $migrationContact->email() . ' already exists');
            } catch (Exception $e) {
                $this->error($contactId . ' - Failed importing contact ' . $migrationContact->email() . ': ' . $e->getMessage());
            }

            $this->info($contactId . ' - Ended importing contact ' . $migrationContact->email());
            $this->info('---');
        }

        return 0;
    }

    private function migrateContact(UuidInterface $contactId, ContactCsvRow $migrationContact): void
    {
        $mapping = $this->getInsightlyMapping($migrationContact);
        if ($mapping === null) {
            return;
        }

        $contact = new Contact(
            $contactId,
            $mapping->id,
            $migrationContact->email(),
            $migrationContact->contactType(),
            $migrationContact->firstName(),
            $migrationContact->lastName()
        );

        $this->contactRepository->save($contact);

        $this->insightlyMappingRepository->save(
            new InsightlyMapping(
                $contactId,
                $migrationContact->insightlyContactId(),
                ResourceType::Contact,
            )
        );
    }

    private function getInsightlyMapping(ContactCsvRow $migrationContact): ?InsightlyMapping
    {
        if ($migrationContact->insightlyProjectId()) {
            try {
                return $this->insightlyMappingRepository->getByInsightlyId($migrationContact->insightlyProjectId());
            } catch (ModelNotFoundException) {
                $this->warn('No mapping found for project ' . $migrationContact->insightlyProjectId());
                return null;
            }
        }

        if ($migrationContact->insightlyOpportunityId()) {
            try {
                return $this->insightlyMappingRepository->getByInsightlyId($migrationContact->insightlyOpportunityId());
            } catch (ModelNotFoundException) {
                $this->warn('No mapping found for opportunity ' . $migrationContact->insightlyOpportunityId());
                return null;
            }
        }

        return null;
    }
}
