<?php

declare(strict_types=1);

namespace Tests\Domain\Integrations\Mappers;

use App\Domain\Integrations\FormRequests\UpdateBillingInfoRequest;
use App\Domain\Integrations\Mappers\UpdateBillingInfoMapper;
use App\Domain\Organizations\Address;
use App\Domain\Organizations\Organization;
use Ramsey\Uuid\Uuid;
use Tests\TestCase;

final class UpdateBillingInfoMapperTest extends TestCase
{
    private array $ids;
    private array $inputs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->ids = [
            'a8ab2245-17b4-44e3-9920-fab075effbdc',
        ];

        $this->inputs = [
            'organisation' => [
                'id' => $this->ids[0],
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
            Uuid::fromString($this->ids[0]),
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
        $request = new UpdateBillingInfoRequest();
        $request->merge($this->inputs);

        $actual = UpdateBillingInfoMapper::map($request);

        $expected = $this->getExpectedOrganization();

        $this->assertEquals($expected, $actual);
    }
}
