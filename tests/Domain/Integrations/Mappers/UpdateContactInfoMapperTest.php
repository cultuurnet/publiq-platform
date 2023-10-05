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
    private string $integrationId;
    private array $inputs;

    protected function setUp(): void
    {
        $this->integrationId = 'd741f32b-58c5-48f5-9b68-b9b867650edd';
        $this->inputs = [
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
    private function getExpectedContacts(): array
    {
        return [
            new Contact(
                Uuid::fromString($this->inputs['functional']['id']),
                Uuid::fromString($this->integrationId),
                $this->inputs['functional']['email'],
                ContactType::from($this->inputs['functional']['type']),
                $this->inputs['functional']['firstName'],
                $this->inputs['functional']['lastName']
            ),
            new Contact(
                Uuid::fromString($this->inputs['technical']['id']),
                Uuid::fromString($this->integrationId),
                $this->inputs['technical']['email'],
                ContactType::from($this->inputs['technical']['type']),
                $this->inputs['technical']['firstName'],
                $this->inputs['technical']['lastName']
            ),
            new Contact(
                Uuid::fromString($this->inputs['contributors'][0]['id']),
                Uuid::fromString($this->integrationId),
                $this->inputs['contributors'][0]['email'],
                ContactType::from($this->inputs['contributors'][0]['type']),
                $this->inputs['contributors'][0]['firstName'],
                $this->inputs['contributors'][0]['lastName']
            ),
            new Contact(
                Uuid::fromString($this->inputs['contributors'][1]['id']),
                Uuid::fromString($this->integrationId),
                $this->inputs['contributors'][1]['email'],
                ContactType::from($this->inputs['contributors'][0]['type']),
                $this->inputs['contributors'][1]['firstName'],
                $this->inputs['contributors'][1]['lastName']
            ),
        ];
    }



    public function test_it_creates_updated_contacts_from_request(): void
    {
        $request = new UpdateContactInfoRequest();
        $request->merge($this->inputs);

        $expected = $this->getExpectedContacts();

        $actual = UpdateContactInfoMapper::map($request, $this->integrationId);

        $this->assertEquals($expected, $actual);
    }

}
