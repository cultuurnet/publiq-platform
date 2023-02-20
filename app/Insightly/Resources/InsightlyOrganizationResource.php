<?php

declare(strict_types=1);

namespace App\Insightly\Resources;

use App\Domain\Organizations\Organization;
use App\Insightly\InsightlyClient;
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

        return $organizationAsArray['ORGANISATION_ID'];
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
}
