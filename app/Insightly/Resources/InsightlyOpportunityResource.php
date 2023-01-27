<?php

declare(strict_types=1);

namespace App\Insightly\Resources;

use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Integration;
use App\Insightly\Exceptions\ContactCannotBeUnlinked;
use App\Insightly\InsightlyClient;
use App\Insightly\Objects\OpportunityStage;
use App\Insightly\Objects\OpportunityState;
use App\Insightly\Serializers\LinkSerializer;
use App\Insightly\Serializers\OpportunitySerializer;
use App\Insightly\Serializers\OpportunityStageSerializer;
use App\Json;
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

    public function updateState(int $id, OpportunityState $state): void
    {
        $opportunityAsArray = $this->get($id);
        $opportunityAsArray['OPPORTUNITY_STATE'] = $state->value;
        $stateRequest = new Request(
            'PUT',
            $this->path,
            [],
            Json::encode($opportunityAsArray)
        );

        $this->insightlyClient->sendRequest($stateRequest);
    }

    public function linkContact(int $opportunityId, int $contactId, ContactType $contactType): void
    {
        $request = new Request(
            'POST',
            $this->path . $opportunityId . '/Links',
            [],
            Json::encode((new LinkSerializer())->contactToLink($contactId, $contactType))
        );

        $this->insightlyClient->sendRequest($request);
    }

    private function getLinks(int $opportunityId): array
    {
        $getLinksRequest = new Request(
            'GET',
            $this->path . $opportunityId . '/Links',
        );
        $getLinksResponse = $this->insightlyClient->sendRequest($getLinksRequest);

        return Json::decodeAssociatively($getLinksResponse->getBody()->getContents());
    }

    /**
     * @throws ContactCannotBeUnlinked
     */
    public function unlinkContact(int $opportunityId, int $contactId): void
    {
        $opportunityLinksAsArray = $this->getLinks($opportunityId);

        $linkId = null;
        foreach ($opportunityLinksAsArray as $opportunityLink) {
            $objectId = $opportunityLink['OBJECT_ID'];
            $linkName = $opportunityLink['LINK_OBJECT_NAME'];
            $linkObjectId = $opportunityLink['LINK_OBJECT_ID'];

            if ($objectId === $opportunityId && $linkName === 'Contact' && $linkObjectId === $contactId) {
                $linkId = $opportunityLink['LINK_ID'];
            }
        }

        if ($linkId === null) {
            throw new ContactCannotBeUnlinked('Contact is not linked to the opportunity.');
        }

        $request = new Request(
            'DELETE',
            $this->path . $opportunityId . '/Links/' . $linkId,
        );

        $this->insightlyClient->sendRequest($request);
    }

    private function get(int $id): array
    {
        $request = new Request(
            'GET',
            $this->path . $id
        );

        $response = $this->insightlyClient->sendRequest($request);

        return Json::decodeAssociatively($response->getBody()->getContents());
    }
}
