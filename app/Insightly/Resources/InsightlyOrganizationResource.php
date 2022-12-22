<?php

declare(strict_types=1);

namespace App\Insightly\Resources;

use App\Domain\Organizations\Organization;
use App\Insightly\InsightlyClient;
use App\Insightly\Serializers\OrganizationSerializer;
use App\Json;
use GuzzleHttp\Psr7\Request;

final class InsightlyOrganizationResource
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

        $contactAsArray = Json::decodeAssociatively($response->getBody()->getContents());

        return $contactAsArray['ORGANISATION_ID'];
    }

    public function update(Organization $organization, int $insightlyId): void
    {
        $request = new Request(
            'PUT',
            $this->path,
            [],
            Json::encode((new OrganizationSerializer())->toInsighltyArrayForUpdate($organization, $insightlyId))
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
