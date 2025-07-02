<?php

declare(strict_types=1);

namespace App\UiTPAS\Listeners;

use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Events\UdbOrganizerCreated;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Integrations\Repositories\UdbOrganizerRepository;
use App\Domain\Mail\Mailer;
use App\Domain\UdbUuid;
use App\Mails\Smtp\MailTemplate;
use App\Search\Sapi3\SearchService;
use App\Search\UdbOrganizerNameResolver;
use App\UiTPAS\Event\UdbOrganizerApproved;
use App\UiTPAS\Event\UdbOrganizerRejected;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Mime\Address;

final class SendMailForUdbOrganizer implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Mailer $mailer,
        private readonly UdbOrganizerRepository $udbOrganizerRepository,
        private readonly IntegrationRepository $integrationRepository,
        private readonly UdbOrganizerNameResolver $udbOrganizerNameResolver,
        private readonly SearchService $searchService,
        private readonly UrlGeneratorContract $urlGenerator,
        private readonly Address $from,
    ) {
    }

    public function handleUdbOrganizerCreated(UdbOrganizerCreated $event): void
    {
        $udbOrganizer = $this->udbOrganizerRepository->getById($event->id);
        $this->sendMail(
            $udbOrganizer->organizerId,
            $udbOrganizer->integrationId,
            MailTemplate::ORGANISATION_UITPAS_REQUESTED->value
        );
    }

    public function handleUdbOrganizerApproved(UdbOrganizerApproved $event): void
    {
        $this->sendMail($event->udbId, $event->integrationId, MailTemplate::ORGANISATION_UITPAS_APPROVED->value);
    }

    public function handleUdbOrganizerRejected(UdbOrganizerRejected $event): void
    {
        // Be careful here, at this point the UdbOrganizer is deleted in the db, which is why we sent both the udbId and the integrationId
        $this->sendMail($event->udbId, $event->integrationId, MailTemplate::ORGANISATION_UITPAS_REJECTED->value);
    }

    private function sendMail(UdbUuid $udbOrganizerId, UuidInterface $integrationId, int $templateId): void
    {
        $integration = $this->integrationRepository->getById($integrationId);

        $organizerName = $this->udbOrganizerNameResolver->getName(
            $this->searchService->findUiTPASOrganizers($udbOrganizerId)
        ) ?? '';

        $sendTo = $integration->filterUniqueContactsWithPreferredContactType(ContactType::Technical);

        foreach ($sendTo as $contact) {
            $this->mailer->send(
                $this->from,
                new Address($contact->email, trim($contact->firstName . ' ' . $contact->lastName)),
                $templateId,
                [
                    'firstName' => $contact->firstName,
                    'integrationName' => $integration->name,
                    'organizerName' => $organizerName,
                    'integrationDetailpage' => $this->urlGenerator->route('nl.integrations.show', $integration->id),
                ]
            );
        }
    }
}
