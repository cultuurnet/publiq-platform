<?php

declare(strict_types=1);

namespace App\Insightly\Resources;

use App\Domain\Organizations\Organization;
use App\Insightly\InsightlyClient;
use App\Insightly\Serializers\CustomFields\InvoiceEmailSerializer;
use App\Insightly\Serializers\CustomFields\VatSerializer;
use App\Insightly\Serializers\OrganizationSerializer;
use App\Json;
use GuzzleHttp\Psr7\Request;

final class InsightlyOrganizationResource implements OrganizationResource
{
    private string $path = 'Organizations/';

    public function __construct(private readonly InsightlyClient $insightlyClient)
    {
    }

    public function create(Organization $organization): int
    {
        $request = new Request(
            'POST',
            $this->path,
            [],
            Json::encode((new OrganizationSerializer())->toInsightlyArray($organization))
        );

        $response = $this->insightlyClient->sendRequest($request);

        $organizationAsArray = Json::decodeAssociatively($response->getBody()->getContents());

        return $organizationAsArray['ORGANIZATION_ID'];
    }

    public function update(Organization $organization, int $id): void
    {
        $request = new Request(
            'PUT',
            $this->path,
            [],
            Json::encode((new OrganizationSerializer())->toInsightlyArrayForUpdate($organization, $id))
        );

        $this->insightlyClient->sendRequest($request);
    }

    public function delete(int $id): void
    {
        $request = new Request(
            'DELETE',
            $this->path . $id
        );

        $this->insightlyClient->sendRequest($request);
    }

    public function findIdByEmail(string $email): ?int
    {
        $request = $this->createSearchRequest(InvoiceEmailSerializer::CUSTOM_FIELD_INVOICE_EMAIL, $email);

        $response = $this->insightlyClient->sendRequest($request);

        $organizationsAsArray = Json::decodeAssociatively($response->getBody()->getContents());

        if (count($organizationsAsArray) < 1) {
            return null;
        }

        return $organizationsAsArray[0]['ORGANIZATION_ID'];
    }

    public function findIdByVat(string $vat): ?int
    {
        $request = $this->createSearchRequest(VatSerializer::CUSTOM_FIELD_VAT, $vat);

        $response = $this->insightlyClient->sendRequest($request);

        $organizationsAsArray = Json::decodeAssociatively($response->getBody()->getContents());

        if (count($organizationsAsArray) < 1) {
            return null;
        }

        return $organizationsAsArray[0]['ORGANIZATION_ID'];
    }

    private function createSearchRequest(string $fieldName, string $fieldValue): Request
    {
        return new Request(
            'GET',
            'Organizations/Search/?field_name=' . $fieldName . '&field_value=' . $fieldValue . '&top=1'
        );
    }
}
