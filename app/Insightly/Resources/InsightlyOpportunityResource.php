<?php

declare(strict_types=1);

namespace App\Insightly\Resources;

use App\Domain\Contacts\ContactType;
use App\Domain\Coupons\Coupon;
use App\Domain\Integrations\Integration;
use App\Domain\Subscriptions\Subscription;
use App\Insightly\Exceptions\ContactCannotBeUnlinked;
use App\Insightly\InsightlyClient;
use App\Insightly\Objects\OpportunityStage;
use App\Insightly\Objects\OpportunityState;
use App\Insightly\Resources\Trait\SyncCustomFields;
use App\Insightly\Serializers\CustomFields\SubscriptionSerializer;
use App\Insightly\Serializers\LinkSerializer;
use App\Insightly\Serializers\OpportunitySerializer;
use App\Insightly\Serializers\OpportunityStageSerializer;
use App\Json;
use GuzzleHttp\Psr7\Request;

final class InsightlyOpportunityResource implements OpportunityResource
{
    use InsightlyLinks;
    use SyncCustomFields;

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

    public function get(int $id): array
    {
        $request = new Request(
            'GET',
            $this->path . $id
        );

        $response = $this->insightlyClient->sendRequest($request);

        return Json::decodeAssociatively($response->getBody()->getContents());
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

    public function updateSubscription(int $id, Subscription $subscription, ?Coupon $coupon): void
    {
        $opportunityAsArray = $this->get($id);

        $opportunityAsArray['CUSTOMFIELDS'] = $this->syncCustomFields(
            $opportunityAsArray['CUSTOMFIELDS'] ?? [],
            (new SubscriptionSerializer())->toInsightlyArray($subscription, $coupon)
        );

        $stateRequest = new Request(
            'PUT',
            $this->path . $id,
            [],
            Json::encode($opportunityAsArray)
        );

        $this->insightlyClient->sendRequest($stateRequest);
    }

    public function update(int $id, Integration $integration): void
    {
        $stageRequest = new Request(
            'PUT',
            $this->path . $id,
            [],
            Json::encode(
                (new OpportunitySerializer($this->insightlyClient->getPipelines()))
                    ->toInsightlyArrayForUpdate($integration, $id)
            )
        );

        $this->insightlyClient->sendRequest($stageRequest);
    }

    public function linkContact(int $id, int $contactId, ContactType $contactType): void
    {
        $request = new Request(
            'POST',
            $this->path . $id . '/Links',
            [],
            Json::encode((new LinkSerializer())->contactToLink($contactId))
        );

        $this->insightlyClient->sendRequest($request);
    }

    /**
     * @throws ContactCannotBeUnlinked
     */
    public function unlinkContact(int $id, int $contactId): void
    {
        $linkId = $this->getLink($id, $contactId, ResourceType::Contact);

        if ($linkId === null) {
            throw new ContactCannotBeUnlinked('Contact is not linked to the opportunity.');
        }

        $request = new Request(
            'DELETE',
            $this->path . $id . '/Links/' . $linkId,
        );

        $this->insightlyClient->sendRequest($request);
    }
}
