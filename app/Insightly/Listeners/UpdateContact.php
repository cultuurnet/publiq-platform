<?php

declare(strict_types=1);

namespace App\Insightly\Listeners;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\Events\ContactUpdated;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Insightly\ContactLink;
use App\Insightly\Exceptions\ContactCannotBeUnlinked;
use App\Insightly\InsightlyClient;
use App\Insightly\InsightlyMapping;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use App\Insightly\SyncIsAllowed;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;
use Throwable;

final class UpdateContact implements ShouldQueue
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

    public function handle(ContactUpdated $contactUpdated): void
    {
        $contact = $this->contactRepository->getById($contactUpdated->id);
        if (!SyncIsAllowed::forContact($contact)) {
            return;
        }

        if ($contactUpdated->emailWasUpdated) {
            $this->handleContactEmailChange($contact);
        } else {
            $this->handleContactUpdate($contact);
        }

        $this->logger->info(
            'Contact updated',
            [
                'domain' => 'insightly',
                'contact_id' => $contactUpdated->id->toString(),
            ]
        );
    }

    private function handleContactEmailChange(Contact $contact): void
    {
        $originalInsightlyContactId = $this->insightlyMappingRepository->getById($contact->id)->insightlyId;
        $insightlyIntegrationId = $this->insightlyMappingRepository->getById($contact->integrationId)->insightlyId;

        $newInsightlyContactId = $this->contactLink->link($contact);

        $this->insightlyMappingRepository->deleteById($contact->id);
        $this->insightlyMappingRepository->save(new InsightlyMapping(
            $contact->id,
            $newInsightlyContactId,
            ResourceType::Contact
        ));

        try {
            $this->insightlyClient->opportunities()->unlinkContact(
                $insightlyIntegrationId,
                $originalInsightlyContactId
            );
        } catch (ContactCannotBeUnlinked $exception) {
            // Contact was not linked to the opportunity, nothing else to do then.
        }

        $this->insightlyClient->opportunities()->linkContact(
            $insightlyIntegrationId,
            $newInsightlyContactId,
            $contact->type
        );
    }

    private function handleContactUpdate(Contact $contact): void
    {
        $insightlyMapping = $this->insightlyMappingRepository->getById($contact->id);
        $this->insightlyClient->contacts()->update($contact, $insightlyMapping->insightlyId);
    }

    public function failed(ContactUpdated $contactUpdated, Throwable $exception): void
    {
        $this->logger->error(
            'Failed to update contact',
            [
                'domain' => 'insightly',
                'contact_id' => $contactUpdated->id->toString(),
                'exception' => $exception,
            ]
        );
    }
}
