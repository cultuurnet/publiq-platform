<?php

declare(strict_types=1);

namespace App\Console\Migrations;

use App\Domain\Auth\Models\UserModel;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Facades\CauserResolver;

final class MigrateUsers extends Command
{
    use ReadCsvFile;

    protected $signature = 'migrate:users';

    protected $description = 'Migrate the users provided in the users.csv CSV file';

    public function handle(): int
    {
        Model::unsetEventDispatcher();

        CauserResolver::setCauser(UserModel::createSystemUser());

        $usersAsArray = $this->readCsvFile('users.csv');

        $usersCount = count($usersAsArray);
        if ($usersCount <= 0) {
            $this->warn('No users to import');
            return 0;
        }

        if (!$this->confirm('Are you sure you want to import ' . $usersCount . ' users?')) {
            return 0;
        }

        foreach ($usersAsArray as $userAsArray) {
            $this->call(
                'migrate:user',
                [
                    'uitId' => $userAsArray[0],
                    '--no-interaction' => true,
                ]
            );
        }

        return 0;
    }
}
