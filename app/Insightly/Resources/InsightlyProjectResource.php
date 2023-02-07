<?php

declare(strict_types=1);

namespace App\Insightly\Resources;

use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Integration;
use App\Insightly\Exceptions\ContactCannotBeUnlinked;
use App\Insightly\InsightlyClient;
use App\Insightly\Objects\ProjectStage;
use App\Insightly\Objects\ProjectState;
use App\Insightly\Serializers\CustomFields\CouponSerializer;
use App\Insightly\Serializers\LinkSerializer;
use App\Insightly\Serializers\ProjectSerializer;
use App\Insightly\Serializers\ProjectStageSerializer;
use App\Json;
use GuzzleHttp\Psr7\Request;

final class InsightlyProjectResource implements ProjectResource
{
    use InsightlyLinks;

    private string $path = 'Projects/';

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
            JSON::encode(
                (new ProjectSerializer($this->insightlyClient->getPipelines()))
                    ->toInsightlyArray($integration)
            )
        );
        $response = $this->insightlyClient->sendRequest($request);

        $projectAsArray = JSON::decodeAssociatively($response->getBody()->getContents());

        return (int) $projectAsArray['PROJECT_ID'];
    }

    public function get(int $id): array
    {
        $request = new Request(
            'GET',
            $this->path . $id
        );

        $response = $this->insightlyClient->sendRequest($request);

        return Json::decodeAssociatively($response->getBody()->getContents());
    }

    public function updateWithCoupon(int $id, string $couponCode): void
    {
        $projectAsArray = $this->get($id);
        $projectAsArray['CUSTOMFIELDS'][] = (new CouponSerializer())->toInsightlyArray($couponCode);

        $request = new Request(
            'PUT',
            $this->path,
            [],
            JSON::encode($projectAsArray)
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

    public function updateStage(int $id, ProjectStage $stage): void
    {
        $stageRequest = new Request(
            'PUT',
            $this->path . $id . '/Pipeline',
            [],
            Json::encode(
                (new ProjectStageSerializer($this->insightlyClient->getPipelines()))
                    ->toInsightlyArray($stage)
            )
        );

        $this->insightlyClient->sendRequest($stageRequest);
    }

    public function updateState(int $id, ProjectState $state): void
    {
        $projectAsArray = $this->get($id);
        $projectAsArray['STATUS'] = $state->value;
        $stateRequest = new Request(
            'PUT',
            $this->path,
            [],
            Json::encode($projectAsArray)
        );

        $this->insightlyClient->sendRequest($stateRequest);
    }

    public function linkOpportunity(int $id, int $opportunityId): void
    {
        $request = new Request(
            'POST',
            $this->path . $id . '/Links',
            [],
            Json::encode((new LinkSerializer())->opportunityToLink($opportunityId))
        );

        $this->insightlyClient->sendRequest($request);
    }

    public function linkContact(int $id, int $contactId, ContactType $contactType): void
    {
        $request = new Request(
            'POST',
            $this->path . $id . '/Links',
            [],
            Json::encode((new LinkSerializer())->contactToLink($contactId, $contactType))
        );

        $this->insightlyClient->sendRequest($request);
    }

    /**
     * @throws ContactCannotBeUnlinked
     */
    public function unlinkContact(int $id, int $contactId): void
    {
        $projectLinks = $this->getLinksForResource($this->path, $id);
        $linkId = $this->getLinkIdForContact($projectLinks, $id, $contactId);

        if ($linkId === null) {
            throw new ContactCannotBeUnlinked('Contact is not linked to the project.');
        }

        $request = new Request(
            'DELETE',
            $this->path . $id . '/Links/' . $linkId,
        );

        $this->insightlyClient->sendRequest($request);
    }
}
