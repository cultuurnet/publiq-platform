<?php

declare(strict_types=1);

namespace App\Insightly\Resources;

use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Integration;
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

    public function updateWithCoupon(int $projectId, string $couponCode): void
    {
        $projectAsArray = $this->get($projectId);
        $projectAsArray['CUSTOMFIELDS'][] = (new CouponSerializer())->toInsightlyArray($couponCode);

        $request = new Request(
            'PUT',
            $this->path,
            [],
            JSON::encode($projectAsArray)
        );

        $this->insightlyClient->sendRequest($request);
    }

    public function delete(int $projectId): void
    {
        $request = new Request(
            'DELETE',
            $this->path . $projectId
        );

        $this->insightlyClient->sendRequest($request);
    }

    public function updateStage(int $projectId, ProjectStage $stage): void
    {
        $stageRequest = new Request(
            'PUT',
            $this->path . $projectId . '/Pipeline',
            [],
            Json::encode(
                (new ProjectStageSerializer($this->insightlyClient->getPipelines()))
                    ->toInsightlyArray($stage)
            )
        );

        $this->insightlyClient->sendRequest($stageRequest);
    }

    public function updateState(int $projectId, ProjectState $state): void
    {
        $projectAsArray = $this->get($projectId);
        $projectAsArray['STATUS'] = $state->value;
        $stateRequest = new Request(
            'PUT',
            $this->path,
            [],
            Json::encode($projectAsArray)
        );

        $this->insightlyClient->sendRequest($stateRequest);
    }

    public function linkOpportunity(int $projectId, int $opportunityId): void
    {
        $request = new Request(
            'POST',
            $this->path . $projectId . '/Links',
            [],
            Json::encode((new LinkSerializer())->opportunityToLink($opportunityId))
        );

        $this->insightlyClient->sendRequest($request);
    }

    public function linkContact(int $projectId, int $contactId, ContactType $contactType): void
    {
        $request = new Request(
            'POST',
            $this->path . $projectId . '/Links',
            [],
            Json::encode((new LinkSerializer())->contactToLink($contactId, $contactType))
        );

        $this->insightlyClient->sendRequest($request);
    }

    private function get(int $projectId): array
    {
        $request = new Request(
            'GET',
            $this->path . $projectId
        );

        $response = $this->insightlyClient->sendRequest($request);

        return Json::decodeAssociatively($response->getBody()->getContents());
    }
}
