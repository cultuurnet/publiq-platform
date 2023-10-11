<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Mappers;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\FormRequests\UpdateContactInfoRequest;
use App\Domain\Integrations\Mappers\UpdateContactInfoMapper;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class UpdateContactInfoMapperTest extends TestCase
{
    private const INTEGRATION_ID = 'd741f32b-58c5-48f5-9b68-b9b867650edd';
    private const FUNCTIONAL_CONTACT_ID = '9442d8cb-b1c7-48ad-b16e-fd88977eaa50';
    private const TECHNICAL_CONTACT_ID = '4e5edef8-1252-4ed5-a66f-27cb8a12ad3f';
    private const FIRST_CONTRIBUTOR_CONTACT_ID = '12ad5738-548a-4248-a05b-f6d451903490';
    private const SECOND_CONTRIBUTOR_CONTACT_ID = '3030864f-a5fd-47e2-ac2b-3bcbc5ea0f5b';

    private function getFullUpdateInputs(): array
    {
        return [
            'functional' => [
                'id' => self::FUNCTIONAL_CONTACT_ID,
                'integrationId' => self::INTEGRATION_ID,
                'email' => 'functional@publiqtest.be',
                'type' => ContactType::Functional->value,
                'firstName' => 'John',
                'lastName' => 'Doe',
            ],
            'technical' => [
                'id' => self::TECHNICAL_CONTACT_ID,
                'integrationId' => self::INTEGRATION_ID,
                'email' => 'technical@publiqtest.be',
                'type' => ContactType::Technical->value,
                'firstName' => 'Jane',
                'lastName' => 'Doe',
            ],
            'contributors' => [
                [
                    'id' => self::FIRST_CONTRIBUTOR_CONTACT_ID,
                    'integrationId' => self::INTEGRATION_ID,
                    'email' => 'technical@publiqtest.be',
                    'type' => ContactType::Contributor->value,
                    'firstName' => 'Kathleen',
                    'lastName' => 'Camden',
                ],
                [
                    'id' => self::SECOND_CONTRIBUTOR_CONTACT_ID,
                    'integrationId' => self::INTEGRATION_ID,
                    'email' => 'technical@publiqtest.be',
                    'type' => ContactType::Contributor->value,
                    'firstName' => 'Harlan',
                    'lastName' => 'Tod',
                ],
            ],
        ];
    }

    /**
     * @return Contact[]
     */
    private function getExpectedContactsForFullUpdate(array $inputs): array
    {
        return [
            new Contact(
                Uuid::fromString($inputs['functional']['id']),
                Uuid::fromString(self::INTEGRATION_ID),
                $inputs['functional']['email'],
                ContactType::from($inputs['functional']['type']),
                $inputs['functional']['firstName'],
                $inputs['functional']['lastName']
            ),
            new Contact(
                Uuid::fromString($inputs['technical']['id']),
                Uuid::fromString(self::INTEGRATION_ID),
                $inputs['technical']['email'],
                ContactType::from($inputs['technical']['type']),
                $inputs['technical']['firstName'],
                $inputs['technical']['lastName']
            ),
            new Contact(
                Uuid::fromString($inputs['contributors'][0]['id']),
                Uuid::fromString(self::INTEGRATION_ID),
                $inputs['contributors'][0]['email'],
                ContactType::from($inputs['contributors'][0]['type']),
                $inputs['contributors'][0]['firstName'],
                $inputs['contributors'][0]['lastName']
            ),
            new Contact(
                Uuid::fromString($inputs['contributors'][1]['id']),
                Uuid::fromString(self::INTEGRATION_ID),
                $inputs['contributors'][1]['email'],
                ContactType::from($inputs['contributors'][0]['type']),
                $inputs['contributors'][1]['firstName'],
                $inputs['contributors'][1]['lastName']
            ),
        ];
    }

    public function test_it_creates_updated_contacts_from_request(): void
    {
        $inputs = $this->getFullUpdateInputs();

        $request = new UpdateContactInfoRequest();
        $request->merge($inputs);

        $expected = $this->getExpectedContactsForFullUpdate($inputs);

        $actual = UpdateContactInfoMapper::map($request, self::INTEGRATION_ID);

        $this->assertEquals($expected, $actual);
    }

    public function test_it_only_updates_functional_contact_from_request(): void
    {
        $updateFunctionalInputs = [
            'functional' => [
                'id' => self::FUNCTIONAL_CONTACT_ID,
                'integrationId' => self::INTEGRATION_ID,
                'email' => 'functional@publiqtest.be',
                'type' => ContactType::Functional->value,
                'firstName' => 'John',
                'lastName' => 'Doe',
            ],
        ];

        $request = new UpdateContactInfoRequest();
        $request->merge($updateFunctionalInputs);

        $actual = UpdateContactInfoMapper::map($request, self::INTEGRATION_ID);

        $expected = [
            new Contact(
                Uuid::fromString($updateFunctionalInputs['functional']['id']),
                Uuid::fromString(self::INTEGRATION_ID),
                $updateFunctionalInputs['functional']['email'],
                ContactType::from($updateFunctionalInputs['functional']['type']),
                $updateFunctionalInputs['functional']['firstName'],
                $updateFunctionalInputs['functional']['lastName']
            ),
        ];

        $this->assertEquals($expected, $actual);
    }

    public function test_it_only_updates_technical_contact_from_request(): void
    {
        $updateTechnicalInputs = [
            'technical' => [
                'id' => self::FUNCTIONAL_CONTACT_ID,
                'integrationId' => self::INTEGRATION_ID,
                'email' => 'technical@publiqtest.be',
                'type' => ContactType::Technical->value,
                'firstName' => 'John',
                'lastName' => 'Doe',
            ],
        ];

        $request = new UpdateContactInfoRequest();
        $request->merge($updateTechnicalInputs);

        $actual = UpdateContactInfoMapper::map($request, self::INTEGRATION_ID);

        $expected = [
            new Contact(
                Uuid::fromString($updateTechnicalInputs['technical']['id']),
                Uuid::fromString(self::INTEGRATION_ID),
                $updateTechnicalInputs['technical']['email'],
                ContactType::from($updateTechnicalInputs['technical']['type']),
                $updateTechnicalInputs['technical']['firstName'],
                $updateTechnicalInputs['technical']['lastName']
            ),
        ];

        $this->assertEquals($expected, $actual);
    }

    public function test_it_only_updates_contributor_contacts_from_request(): void
    {
        $updateContributorsInputs = [
            'contributors' => [
                [
                    'id' => self::TECHNICAL_CONTACT_ID,
                    'integrationId' => self::INTEGRATION_ID,
                    'email' => 'technical@publiqtest.be',
                    'type' => ContactType::Contributor->value,
                    'firstName' => 'Kathleen',
                    'lastName' => 'Camden',
                ],
                [
                    'id' => self::TECHNICAL_CONTACT_ID,
                    'integrationId' => self::INTEGRATION_ID,
                    'email' => 'technical@publiqtest.be',
                    'type' => ContactType::Contributor->value,
                    'firstName' => 'Harlan',
                    'lastName' => 'Tod',
                ],
            ],
        ];

        $request = new UpdateContactInfoRequest();
        $request->merge($updateContributorsInputs);

        $actual = UpdateContactInfoMapper::map($request, self::INTEGRATION_ID);

        $expected = [
            new Contact(
                Uuid::fromString($updateContributorsInputs['contributors'][0]['id']),
                Uuid::fromString(self::INTEGRATION_ID),
                $updateContributorsInputs['contributors'][0]['email'],
                ContactType::from($updateContributorsInputs['contributors'][0]['type']),
                $updateContributorsInputs['contributors'][0]['firstName'],
                $updateContributorsInputs['contributors'][0]['lastName']
            ),
            new Contact(
                Uuid::fromString($updateContributorsInputs['contributors'][1]['id']),
                Uuid::fromString(self::INTEGRATION_ID),
                $updateContributorsInputs['contributors'][1]['email'],
                ContactType::from($updateContributorsInputs['contributors'][1]['type']),
                $updateContributorsInputs['contributors'][1]['firstName'],
                $updateContributorsInputs['contributors'][1]['lastName']
            ),
        ];

        $this->assertEquals($expected, $actual);
    }
}
