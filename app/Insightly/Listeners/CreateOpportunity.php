<?php

declare(strict_types=1);

namespace App\Insightly\Listeners;

use App\Domain\Contacts\Repositories\ContactRepository;
use App\Domain\Integrations\Events\IntegrationCreated;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Insightly\InsightlyClient;
use Illuminate\Contracts\Queue\ShouldQueue;

final class CreateOpportunity implements ShouldQueue
{
    public function __construct(
        private readonly InsightlyClient $insightlyClient,
        private readonly IntegrationRepository $integrationRepository,
        private readonly ContactRepository $contactRepository
    ) {
    }

    public function handle(IntegrationCreated $integrationCreated): void
    {
        if (empty(config('insightly.api_key'))) {
            return;
        }

        $this->insightlyClient->opportunities()->create(
            $this->integrationRepository->getById($integrationCreated->id)
        );

        $contacts = $this->contactRepository->getByIntegrationId($integrationCreated->id);
        foreach ($contacts as $contact) {
            $this->insightlyClient->contacts()->create($contact);
        }
    }
}
