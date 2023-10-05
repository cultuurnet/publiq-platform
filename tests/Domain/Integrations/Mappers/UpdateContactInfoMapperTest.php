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
    private string $integrationId = 'd741f32b-58c5-48f5-9b68-b9b867650edd';

    private function getFullUpdateInputs()
    {
        return [
            'functional' => [
                'id' => '9442d8cb-b1c7-48ad-b16e-fd88977eaa50',
                'integrationId' => $this->integrationId,
                'email' => 'functional@publiqtest.be',
                'type' => ContactType::Functional->value,
                'firstName' => 'John',
                'lastName' => 'Doe',
            ],
            'technical' => [
                'id' => 'd741f32b-58c5-48f5-9b68-b9b867650edd',
                'integrationId' => $this->integrationId,
                'email' => 'technical@publiqtest.be',
                'type' => ContactType::Technical->value,
                'firstName' => 'Jane',
                'lastName' => 'Doe',
            ],
            'contributors' => [
                [
                    'id' => 'd741f32b-58c5-48f5-9b68-b9b867650edd',
                    'integrationId' => $this->integrationId,
                    'email' => 'technical@publiqtest.be',
                    'type' => ContactType::Contributor->value,
                    'firstName' => 'Kathleen',
                    'lastName' => 'Camden',
                ],
                [
                    'id' => 'd741f32b-58c5-48f5-9b68-b9b867650edd',
                    'integrationId' => $this->integrationId,
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
                Uuid::fromString($this->integrationId),
                $inputs['functional']['email'],
                ContactType::from($inputs['functional']['type']),
                $inputs['functional']['firstName'],
                $inputs['functional']['lastName']
            ),
            new Contact(
                Uuid::fromString($inputs['technical']['id']),
                Uuid::fromString($this->integrationId),
                $inputs['technical']['email'],
                ContactType::from($inputs['technical']['type']),
                $inputs['technical']['firstName'],
                $inputs['technical']['lastName']
            ),
            new Contact(
                Uuid::fromString($inputs['contributors'][0]['id']),
                Uuid::fromString($this->integrationId),
                $inputs['contributors'][0]['email'],
                ContactType::from($inputs['contributors'][0]['type']),
                $inputs['contributors'][0]['firstName'],
                $inputs['contributors'][0]['lastName']
            ),
            new Contact(
                Uuid::fromString($inputs['contributors'][1]['id']),
                Uuid::fromString($this->integrationId),
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

        $actual = UpdateContactInfoMapper::map($request, $this->integrationId);

        $this->assertEquals($expected, $actual);
    }

    public function test_it_only_updates_functional_contact_from_request(): void
    {
        $updateFunctionalInputs = [
            'functional' => [
                'id' => '9442d8cb-b1c7-48ad-b16e-fd88977eaa50',
                'integrationId' => $this->integrationId,
                'email' => 'functional@publiqtest.be',
                'type' => ContactType::Functional->value,
                'firstName' => 'John',
                'lastName' => 'Doe',
            ],
        ];

        $request = new UpdateContactInfoRequest();
        $request->merge($updateFunctionalInputs);

        $actual = UpdateContactInfoMapper::map($request, $this->integrationId);

        $expected = [
            new Contact(
                Uuid::fromString($updateFunctionalInputs['functional']['id']),
                Uuid::fromString($this->integrationId),
                $updateFunctionalInputs['functional']['email'],
                ContactType::from($updateFunctionalInputs['functional']['type']),
                $updateFunctionalInputs['functional']['firstName'],
                $updateFunctionalInputs['functional']['lastName']
            ),
        ];

        $this->assertEquals($expected, $actual);
    }

}
