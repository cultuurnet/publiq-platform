<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Mappers;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\FormRequests\UpdateContactInfoRequest;
use Ramsey\Uuid\Uuid;

final class UpdateContactInfoMapper
{
    /**
     * @return array<Contact>
     */
    public static function map(UpdateContactInfoRequest $request, string $integrationId): array
    {
        /**
         * @var array<Contact> $contacts
         */
        $contacts = [];

        if ($request->input('functional.id') !== null) {
            $contactId = $request->input('functional.id');

            $contact = new Contact(
                Uuid::fromString($contactId),
                Uuid::fromString($integrationId),
                $request->input('functional.email'),
                ContactType::from($request->input('functional.type')),
                $request->input('functional.firstName'),
                $request->input('functional.lastName')
            );

            $contacts[] = $contact;
        }

        if ($request->input('technical.id') !== null) {
            $contactId = $request->input('technical.id');

            $contact = new Contact(
                Uuid::fromString($contactId),
                Uuid::fromString($integrationId),
                $request->input('technical.email'),
                ContactType::from($request->input('technical.type')),
                $request->input('technical.firstName'),
                $request->input('technical.lastName')
            );

            $contacts[] = $contact;
        }

        $contributors = $request->input('contributors');

        foreach ($contributors as $contributor) {
            $contactId = $contributor['id'];

            $contact = new Contact(
                Uuid::fromString($contactId),
                Uuid::fromString($integrationId),
                $contributor['email'],
                ContactType::from($contributor['type']),
                $contributor['firstName'],
                $contributor['lastName']
            );

            $contacts[] = $contact;
        }

        $newLastName = $request->input('newContributorLastName');
        $newFirstName = $request->input('newContributorFirstName');
        $newEmail = $request->input('newContributorEmail');

        if ($newLastName !== null && $newFirstName !== null && $newEmail !== null) {
            $contact = new Contact(
                Uuid::uuid4(),
                Uuid::fromString($integrationId),
                $newEmail,
                ContactType::Contributor,
                $newFirstName,
                $newLastName
            );

            $contacts[] = $contact;
        }

        return $contacts;
    }
}
