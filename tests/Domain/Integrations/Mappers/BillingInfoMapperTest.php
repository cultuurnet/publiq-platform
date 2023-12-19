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
    private const ORGANISATION_ID = 'a8ab2245-17b4-44e3-9920-fab075effbdc';

    private array $inputs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->inputs = [
            'organisation' => [
                'id' => self::ORGANISATION_ID,
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
            Uuid::fromString(self::ORGANISATION_ID),
            $this->inputs['organisation']['name'],
            $this->inputs['organisation']['invoiceEmail'],
            $this->inputs['organisation']['vat'],
            new Address(
                $this->inputs['organisation']['address']['street'],
                $this->inputs['organisation']['address']['zip'],
                $this->inputs['organisation']['address']['city'],
                $this->inputs['organisation']['address']['country'],
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
        Uuid::setFactory(new UuidTestFactory(['uuid4' => [self::ORGANISATION_ID]]));

        unset($this->inputs['organisation']['id']);
        $request = new CreateBillingInfoRequest($this->inputs);

        $actual = BillingInfoMapper::mapCreate($request);

        $this->assertEquals($this->getExpectedOrganization(), $actual);

        Uuid::setFactory(new UuidFactory());
    }
}
