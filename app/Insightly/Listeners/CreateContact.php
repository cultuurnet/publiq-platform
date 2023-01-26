<?php

declare(strict_types=1);

namespace App\Insightly\Listeners;

use App\Domain\Contacts\Events\ContactCreated;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Insightly\ContactLink;
use App\Insightly\InsightlyClient;
use App\Insightly\InsightlyMapping;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use App\Insightly\SyncIsAllowed;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class CreateContact implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly InsightlyClient $insightlyClient,
        private readonly ContactLink $contactLink,
        private readonly ContactRepository $contactRepository,
        private readonly InsightlyMappingRepository $insightlyMappingRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(ContactCreated $contactCreated): void
    {
        $contact = $this->contactRepository->getById($contactCreated->id);
        if (!SyncIsAllowed::forContact($contact)) {
            return;
        }

        $contactInsightlyId = $this->contactLink->link($contact);

        $this->insightlyMappingRepository->save(new InsightlyMapping(
            $contactCreated->id,
            $contactInsightlyId,
            ResourceType::Contact
        ));

        $integrationMapping = $this->insightlyMappingRepository->getByIdAndType($contact->integrationId, ResourceType::Opportunity);
        $this->insightlyClient->opportunities()->linkContact(
            $integrationMapping->insightlyId,
            $contactInsightlyId,
            $contact->type
        );

        $this->logger->info(
            'Contact created',
            [
                'domain' => 'insightly',
                'contact_id' => $contactCreated->id->toString(),
            ]
        );
    }

    public function failed(ContactCreated $contactCreated, \Throwable $exception): void
    {
        $this->logger->error(
            'Failed to create contact',
            [
                'domain' => 'insightly',
                'contact_id' => $contactCreated->id->toString(),
                'exception' => $exception,
            ]
        );
    }
}
