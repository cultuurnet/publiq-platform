<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Mappers;

use App\Domain\Integrations\FormRequests\CreateBillingInfoRequest;
use App\Domain\Integrations\FormRequests\UpdateBillingInfoRequest;
use App\Domain\Integrations\Mappers\BillingInfoMapper;
use App\Domain\Organizations\Address;
use App\Domain\Organizations\Organization;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use Tests\TestCase;
use Tests\UuidTestFactory;

final class BillingInfoMapperTest extends TestCase
{
    private const ORGANIZATION_ID = 'a8ab2245-17b4-44e3-9920-fab075effbdc';

    private array $inputs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inputs = [
            'organization' => [
                'id' => self::ORGANIZATION_ID,
                'name' => 'publiq vzw',
                'invoiceEmail' => 'info@publiqtest.be',
                'vat' => 'BE 0475 250 609',
                'address' => [
                    'street' => 'Henegouwenkaai 41-43',
                    'zip' => '1080',
                    'city' => 'Brussel',
                    'country' => 'Belgium',
                ],
            ],
        ];
    }

    private function getExpectedOrganization(): Organization
    {
        return new Organization(
            Uuid::fromString(self::ORGANIZATION_ID),
            $this->inputs['organization']['name'],
            $this->inputs['organization']['invoiceEmail'],
            $this->inputs['organization']['vat'],
            new Address(
                $this->inputs['organization']['address']['street'],
                $this->inputs['organization']['address']['zip'],
                $this->inputs['organization']['address']['city'],
                $this->inputs['organization']['address']['country'],
            )
        );
    }

    public function test_it_creates_an_organisation_with_updated_billing_info_from_request(): void
    {
        $request = new UpdateBillingInfoRequest($this->inputs);

        $actual = BillingInfoMapper::mapUpdate($request);

        $this->assertEquals($this->getExpectedOrganization(), $actual);
    }

    public function test_it_creates_organization_from_create_billing_info_form_request(): void
    {
        Uuid::setFactory(new UuidTestFactory(['uuid4' => [self::ORGANIZATION_ID]]));

        unset($this->inputs['organization']['id']);
        $request = new CreateBillingInfoRequest($this->inputs);

        $actual = BillingInfoMapper::mapCreate($request);

        $this->assertEquals($this->getExpectedOrganization(), $actual);

        Uuid::setFactory(new UuidFactory());
    }
}
