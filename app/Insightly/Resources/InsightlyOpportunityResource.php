<?php

declare(strict_types=1);

namespace App\Insightly\Resources;

use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Integration;
use App\Insightly\Interfaces\CrmClient;
use App\Insightly\Interfaces\OpportunityResource;
use App\Insightly\Serializers\LinkSerializer;
use App\Json;
use App\Insightly\Objects\OpportunityStage;
use App\Insightly\Serializers\OpportunitySerializer;
use App\Insightly\Serializers\OpportunityStageSerializer;
use GuzzleHttp\Psr7\Request;

final class InsightlyOpportunityResource implements OpportunityResource
{
    private string $path = 'Opportunities/';

    public function __construct(
        private readonly InsightlyClient $insightlyClient,
    ) {
    }

    public function create(Integration $integration): int
    {
        $request = new Request(
            'POST',
            $this->path,
            [],
            Json::encode(
                (new OpportunitySerializer($this->insightlyClient->getPipelines()))
                    ->toInsightlyArray($integration)
            )
        );

        $response = $this->insightlyClient->sendRequest($request);

        $opportunityAsArray = Json::decodeAssociatively($response->getBody()->getContents());

        $id = (int) $opportunityAsArray['OPPORTUNITY_ID'];

        $this->updateStage($id, OpportunityStage::TEST);

        return $id;
    }

    public function delete(int $id): void
    {
        $request = new Request(
            'DELETE',
            $this->path . $id
        );

        $this->insightlyClient->sendRequest($request);
    }

    public function updateStage(int $id, OpportunityStage $stage): void
    {
        $stageRequest = new Request(
            'PUT',
            $this->path . $id . '/Pipeline',
            [],
            Json::encode(
                (new OpportunityStageSerializer($this->insightlyClient->getPipelines()))
                    ->toInsightlyArray($stage)
            )
        );

        $this->insightlyClient->sendRequest($stageRequest);
    }

    public function linkContact(int $opportunityId, int $contactId, ContactType $contactType): void
    {
        $request = new Request(
            'POST',
            'Opportunities/' . $opportunityId . '/Links',
            [],
            Json::encode((new LinkSerializer())->contactToLink($contactId, $contactType))
        );

        $this->insightlyClient->sendRequest($request);
    }
}
