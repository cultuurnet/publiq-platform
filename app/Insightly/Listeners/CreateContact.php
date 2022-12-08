<?php

declare(strict_types=1);

namespace App\Insightly\Listeners;

use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Events\ContactCreated;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Insightly\InsightlyClient;
use App\Insightly\InsightlyMapping;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

final class CreateContact implements ShouldQueue
{
    private array $allowedContactTypes = [
        ContactType::Technical,
        ContactType::Functional,
    ];

    public function __construct(
        private readonly InsightlyClient $insightlyClient,
        private readonly ContactRepository $contactRepository,
        private readonly InsightlyMappingRepository $insightlyMappingRepository
    ) {
    }

    public function handle(ContactCreated $contactCreated): void
    {
        if (empty(config('insightly.api_key'))) {
            return;
        }

        $contact = $this->contactRepository->getById($contactCreated->id);
        if (!in_array($contact->type, $this->allowedContactTypes, true)) {
            return;
        }

        $contactInsightlyId = $this->insightlyClient->contacts()->create($contact);
        $this->insightlyMappingRepository->save(new InsightlyMapping(
            $contactCreated->id,
            $contactInsightlyId,
            ResourceType::Contact
        ));

        $integrationMapping = $this->insightlyMappingRepository->getById($contact->integrationId);
        $this->insightlyClient->opportunities()->linkContact(
            $integrationMapping->insightlyId,
            $contactInsightlyId,
            $contact->type
        );

        Log::info(
            'Contact created',
            [
                'domain' => 'insightly',
                'contact_id' => $contactCreated->id->toString(),
            ]
        );
    }

    public function failed(ContactCreated $contactCreated, \Throwable $exception): void
    {
        Log::error(
            'Failed to create contact',
            [
                'domain' => 'insightly',
                'contact_id' => $contactCreated->id->toString(),
                'exception' => $exception,
            ]
        );
    }
}
