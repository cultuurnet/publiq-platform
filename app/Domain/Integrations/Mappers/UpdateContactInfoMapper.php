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
    public static function map(UpdateContactInfoRequest $updateContactInfo, string $integrationId): array
    {
        /**
         * @var array<Contact> $contacts
         */
        $contacts = [];

        if ($updateContactInfo->input('functional.id') !== null) {
            $contactId = $updateContactInfo->input('functional.id');

            $contact = new Contact(
                Uuid::fromString($contactId),
                Uuid::fromString($integrationId),
                $updateContactInfo->input('functional.email'),
                ContactType::from($updateContactInfo->input('functional.type')),
                $updateContactInfo->input('functional.firstName'),
                $updateContactInfo->input('functional.lastName')
            );

            $contacts[] = $contact;
        }

        if ($updateContactInfo->input('technical.id') !== null) {
            $contactId = $updateContactInfo->input('technical.id');

            $contact = new Contact(
                Uuid::fromString($contactId),
                Uuid::fromString($integrationId),
                $updateContactInfo->input('technical.email'),
                ContactType::from($updateContactInfo->input('technical.type')),
                $updateContactInfo->input('technical.firstName'),
                $updateContactInfo->input('technical.lastName')
            );

            $contacts[] = $contact;
        }

        $contributors = $updateContactInfo->input('contributors');

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

        $newLastName = $updateContactInfo->input('newContributorLastName');
        $newFirstName = $updateContactInfo->input('newContributorFirstName');
        $newEmail = $updateContactInfo->input('newContributorEmail');

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
