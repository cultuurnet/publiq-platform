<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Contacts\Repositories\ContactKeyVisibilityRepository;
use App\Domain\Integrations\KeyVisibility;
use Illuminate\Database\Seeder;

final class ContactsKeyVisibilitySeeder extends Seeder
{
    public function run(ContactKeyVisibilityRepository $contactKeyVisibilityRepository): void
    {
        $contactKeyVisibilityRepository->save('dev+e2etest-v1@publiq.be', KeyVisibility::v1);
    }
}
