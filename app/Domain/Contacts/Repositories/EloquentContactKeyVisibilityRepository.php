<?php

declare(strict_types=1);

namespace App\Domain\Contacts\Repositories;

use App\Domain\Contacts\Models\ContactKeyVisibilityModel;
use App\Domain\Integrations\KeyVisibility;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class EloquentContactKeyVisibilityRepository implements ContactKeyVisibilityRepository
{
    public function findByEmail(string $email): KeyVisibility
    {
        try {
            /** @var ContactKeyVisibilityModel $contactKeyVisibility */
            $contactKeyVisibility = ContactKeyVisibilityModel::query()
                ->where('email', $email)
                ->firstOrFail();
            return $contactKeyVisibility->key_visibility;
        } catch (ModelNotFoundException) {
            return KeyVisibility::v2;
        }
    }
}
