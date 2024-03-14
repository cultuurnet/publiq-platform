<?php

declare(strict_types=1);

namespace App\Console\Commands\Migrations;

use App\Domain\Auth\Models\UserModel;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;
use Spatie\Activitylog\Facades\CauserResolver;

final class MigrateContacts extends Command
{
    use ReadCsvFile;

    protected $signature = 'migrate:contacts';

    protected $description = 'Migrate contacts provided in the contacts.csv file (database/project-aanvraag/contacts.csv)';

    public function handle(): int
    {
        Model::unsetEventDispatcher();

        CauserResolver::setCauser(UserModel::createSystemUser());

        $this->info('Migrating contacts...');

        $contacts = $this->readCsvFile('database/project-aanvraag/contacts.csv');
        $migrationContacts = array_map(fn (array $contact) => new MigrationContact($contact), $contacts);
        $contactsCount = count($migrationContacts);

        if ($contactsCount <= 0) {
            $this->warn('No contacts to import');
            return 0;
        }

        if (!$this->confirm('Are you sure you want to import ' . $contactsCount . ' contacts?')) {
            return 0;
        }

        foreach ($migrationContacts as $migrationContact) {
            $contactId = Uuid::uuid4();

            $this->info($contactId . ' - Started importing contact ' . $migrationContact->email());

        }

        $this->info('Migrated ' . count($contacts) . ' contacts');

        return 1;
    }
}
