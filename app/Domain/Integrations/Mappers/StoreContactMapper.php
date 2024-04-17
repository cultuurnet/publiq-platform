<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Mappers;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\FormRequests\StoreContactRequest;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class StoreContactMapper
{
    public static function map(StoreContactRequest $request, UuidInterface $integrationId): Contact
    {

        return new Contact(
            Uuid::uuid4(),
            $integrationId,
            $request->input('email'),
            ContactType::Contributor,
            $request->input('firstName'),
            $request->input('lastName'),
        );

    }
}
