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
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\UuidInterface;
use Throwable;

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

    /**
     * @return int The Insightly id of the newly created contact
     */
    private function storeAndLinkContactAtInsightly(Contact $contact): int
    {
        $insightlyContacts = $this->insightlyClient->contacts()->findByEmail($contact->email);

        if ($insightlyContacts->isEmpty()) {
            $contactInsightlyId = $this->insightlyClient->contacts()->create($contact);
        } else {
            $contactInsightlyId = $insightlyContacts->mostLinks()->insightlyId;

            $this->insightlyClient->contacts()->update($contact, $contactInsightlyId);
        }

        $this->insightlyMappingRepository->save(new InsightlyMapping(
            $contact->id,
            $contactInsightlyId,
            ResourceType::Contact
        ));

        try {
            $opportunityMapping = $this->insightlyMappingRepository->getByIdAndType($contact->integrationId, ResourceType::Opportunity);
            $this->insightlyClient->opportunities()->linkContact(
                $opportunityMapping->insightlyId,
                $contactInsightlyId,
                $contact->type
            );
        } catch (ModelNotFoundException $exception) {
            // No mapping exists so it can't be linked.
        }

        try {
            $projectMapping = $this->insightlyMappingRepository->getByIdAndType($contact->integrationId, ResourceType::Project);
            $this->insightlyClient->projects()->linkContact(
                $projectMapping->insightlyId,
                $contactInsightlyId
            );
        } catch (ModelNotFoundException) {
            // No mapping exists so it can't be linked.
        }

        return $contactInsightlyId;
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

        $this->insightlyMappingRepository->deleteById($contact->id);
        $this->unlinkContactFromOpportunity($contact->integrationId, $oldInsightlyContactId);
        $this->unlinkContactFromProject($contact->integrationId, $oldInsightlyContactId);

        $newInsightlyContactId = $this->storeAndLinkContactAtInsightly($contact);

        $this->insightlyClient->contacts()->linkContact($newInsightlyContactId, $oldInsightlyContactId);
    }

    private function unlinkContactFromOpportunity(UuidInterface $integrationId, int $insightlyContactId): void
    {
        try {
            $insightlyOpportunityId = $this->insightlyMappingRepository->getByIdAndType(
                $integrationId,
                ResourceType::Opportunity
            )->insightlyId;
            $this->insightlyClient->opportunities()->unlinkContact($insightlyOpportunityId, $insightlyContactId);
        } catch (ModelNotFoundException|ContactCannotBeUnlinked $exception) {
            // Contact was not linked to the opportunity, nothing else to do then.
        }
    }

    private function unlinkContactFromProject(UuidInterface $integrationId, int $insightlyContactId): void
    {
        try {
            $insightlyProjectId = $this->insightlyMappingRepository->getByIdAndType(
                $integrationId,
                ResourceType::Project
            )->insightlyId;
            $this->insightlyClient->projects()->unlinkContact($insightlyProjectId, $insightlyContactId);
        } catch (ModelNotFoundException|ContactCannotBeUnlinked $exception) {
            // Contact was not linked to the opportunity, nothing else to do then.
        }
    }

    public function failed(ContactUpdated|ContactCreated $event, Throwable $exception): void
    {
        $logMessage = 'Failed to create contact';
        if ($event instanceof ContactUpdated) {
            $logMessage = 'Failed to update contact';
        }

        $this->logger->error(
            $logMessage,
            [
                'domain' => 'insightly',
                'contact_id' => $event->id->toString(),
                'exception' => $exception,
            ]
        );
    }
}
