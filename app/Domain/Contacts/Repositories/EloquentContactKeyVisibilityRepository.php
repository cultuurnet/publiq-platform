<?php

declare(strict_types=1);

namespace App\Domain\Contacts\Repositories;

use App\Domain\Contacts\Models\ContactKeyVisibilityModel;
use App\Domain\Integrations\KeyVisibility;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Ramsey\Uuid\Uuid;

final class EloquentContactKeyVisibilityRepository implements ContactKeyVisibilityRepository
{
    public function save(string $email, KeyVisibility $keyVisibility): void
    {
        ContactKeyVisibilityModel::query()->updateOrCreate([
            'id' => Uuid::uuid4(),
            'email' => $email,
            'key_visibility' => $keyVisibility,
        ]);
    }

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
