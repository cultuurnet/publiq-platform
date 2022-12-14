<?php

declare(strict_types=1);

namespace App\Insightly\Resources;

use App\Domain\Integrations\Integration;
use App\Insightly\InsightlyClient;
use App\Insightly\Objects\ProjectStage;
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

        $id = (int) $projectAsArray['PROJECT_ID'];

        $this->updateStage($id, ProjectStage::TEST);

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
}
