<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Auth\Models\UserModel;
use Illuminate\Database\Seeder;
use Spatie\Activitylog\Facades\CauserResolver;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        CauserResolver::setCauser(UserModel::createSystemUser());

        $this->call([
            SubscriptionsSeeder::class,
            ContactsKeyVisibilitySeeder::class,
        ]);
    }
}
