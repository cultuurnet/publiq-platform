<?php

declare(strict_types=1);

namespace App\Insightly\Listeners;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\Events\ContactCreated;
use App\Domain\Contacts\Events\ContactUpdated;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Insightly\Exceptions\ContactCannotBeUnlinked;
use App\Insightly\InsightlyClient;
use App\Insightly\InsightlyMapping;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\Resources\ResourceType;
use App\Insightly\SyncIsAllowed;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Arr;
use Psr\Log\LoggerInterface;

final class SyncContact implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly InsightlyClient $insightlyClient,
        private readonly ContactRepository $contactRepository,
        private readonly InsightlyMappingRepository $insightlyMappingRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handleContactCreated(ContactCreated $contactCreated): void
    {
        $contact = $this->contactRepository->getById($contactCreated->id);
        if (!SyncIsAllowed::forContact($contact)) {
            return;
        }

        $this->storeAndLinkContactAtInsightly($contact);

        $this->logger->info(
            'Contact created',
            [
                'domain' => 'insightly',
                'contact_id' => $contactCreated->id->toString(),
            ]
        );
    }

    private function storeAndLinkContactAtInsightly(Contact $contact): void
    {
        $contactIds = $this->insightlyClient->contacts()->findIdsByEmail($contact->email);

        if (empty($contactIds)) {
            $contactInsightlyId = $this->insightlyClient->contacts()->create($contact);
        } else {
            $contactIds = array_values(Arr::sort($contactIds));
            $contactInsightlyId = $contactIds[0];
        }

        $this->insightlyMappingRepository->save(new InsightlyMapping(
            $contact->id,
            $contactInsightlyId,
            ResourceType::Contact
        ));

        $integrationMapping = $this->insightlyMappingRepository->getByIdAndType($contact->integrationId, ResourceType::Opportunity);
        $this->insightlyClient->opportunities()->linkContact(
            $integrationMapping->insightlyId,
            $contactInsightlyId,
            $contact->type
        );
    }

    public function handleContactUpdated(ContactUpdated $contactUpdated): void
    {
        $contact = $this->contactRepository->getById($contactUpdated->id);
        if (!SyncIsAllowed::forContact($contact)) {
            return;
        }

        if ($contactUpdated->emailWasUpdated) {
            $this->handleEmailChange($contact);
        } else {
            $this->handleRegularUpdate($contact);
        }

        $this->logger->info(
            'Contact updated',
            [
                'domain' => 'insightly',
                'contact_id' => $contactUpdated->id->toString(),
            ]
        );
    }

    private function handleRegularUpdate(Contact $contact): void
    {
        $insightlyMapping = $this->insightlyMappingRepository->getByIdAndType($contact->id, ResourceType::Contact);
        $this->insightlyClient->contacts()->update($contact, $insightlyMapping->insightlyId);
    }

    private function handleEmailChange(Contact $contact): void
    {
        $oldInsightlyContactId = $this->insightlyMappingRepository->getByIdAndType(
            $contact->id,
            ResourceType::Contact
        )->insightlyId;
        $insightlyIntegrationId = $this->insightlyMappingRepository->getByIdAndType(
            $contact->integrationId,
            ResourceType::Opportunity
        )->insightlyId;

        $this->insightlyMappingRepository->deleteById($contact->id);
        try {
            $this->insightlyClient->opportunities()->unlinkContact($insightlyIntegrationId, $oldInsightlyContactId);
        } catch (ContactCannotBeUnlinked $exception) {
            // Contact was not linked to the opportunity, nothing else to do then.
        }

        $this->storeAndLinkContactAtInsightly($contact);
    }
}
