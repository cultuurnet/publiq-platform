<?php

declare(strict_types=1);

namespace App\UiTPAS\Listeners;

use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Events\IntegrationActivationRequested;
use App\Domain\Integrations\IntegrationStatus;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Mail\Mailer;
use App\Domain\UdbUuid;
use App\Mails\Template\MailTemplate;
use App\Mails\Template\TemplateName;
use App\Search\Sapi3\SearchService;
use App\Search\UdbOrganizerNameResolver;
use App\UiTPAS\Event\UdbOrganizerApproved;
use App\UiTPAS\Event\UdbOrganizerRejected;
use App\UiTPAS\Event\UdbOrganizerRequested;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Routing\UrlGenerator as UrlGeneratorContract;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Mime\Address;

final class SendUiTPASMails implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Mailer $mailer,
        private readonly IntegrationRepository $integrationRepository,
        private readonly UdbOrganizerNameResolver $udbOrganizerNameResolver,
        private readonly SearchService $searchService,
        private readonly UrlGeneratorContract $urlGenerator,
        private readonly Address $from,
    ) {
    }

    public function handleIntegrationActivationRequested(IntegrationActivationRequested $event): void
    {
        $integration = $this->integrationRepository->getById($event->id);
        if ($integration->type !== IntegrationType::UiTPAS) {
            return;
        }

        $organizerNames = array_filter(array_map(
            fn ($udbOrganizer) => $this->udbOrganizerNameResolver->getName(
                $this->searchService->findOrganizers($udbOrganizer->organizerId)
            ),
            $integration->udbOrganizers()
        ));

        $this->sendMail(
            $integration->id,
            TemplateName::ORGANISATION_UITPAS_REQUESTED,
            implode(', ', $organizerNames)
        );
    }

    public function handleUdbOrganizerRequested(UdbOrganizerRequested $event): void
    {
        $integration = $this->integrationRepository->getById($event->integrationId);
        $udbOrganizer = $integration->getUdbOrganizerByOrgId($event->udbId);

        if ($integration->status !== IntegrationStatus::Active) {
            // Pending integrations are handled by the IntegrationActivationRequested event.
            return;
        }

        if ($udbOrganizer === null) {
            // If the organizer is not found, we cannot send a mail.
            return;
        }

        $this->sendMailWithSingleOrganizer(
            $event->integrationId,
            $event->udbId,
            TemplateName::ORGANISATION_UITPAS_REQUESTED
        );
    }

    public function handleUdbOrganizerApproved(UdbOrganizerApproved $event): void
    {
        $this->sendMailWithSingleOrganizer(
            $event->integrationId,
            $event->udbId,
            TemplateName::ORGANISATION_UITPAS_APPROVED
        );
    }

    public function handleUdbOrganizerRejected(UdbOrganizerRejected $event): void
    {
        $this->sendMailWithSingleOrganizer(
            $event->integrationId,
            $event->udbId,
            TemplateName::ORGANISATION_UITPAS_REJECTED
        );
    }

    private function sendMailWithSingleOrganizer(
        UuidInterface $integrationId,
        UdbUuid $udbOrganizerId,
        TemplateName $template
    ): void {
        $integration = $this->integrationRepository->getById($integrationId);
        if ($integration->type !== IntegrationType::UiTPAS) {
            return;
        }

        $organizerName = $this->udbOrganizerNameResolver->getName(
            $this->searchService->findOrganizers($udbOrganizerId)
        ) ?? '';

        $this->sendMail($integrationId, $template, $organizerName);
    }

    private function sendMail(
        UuidInterface $integrationId,
        TemplateName $templateName,
        ?string $organizerName = null
    ): void {
        $integration = $this->integrationRepository->getById($integrationId);
        if ($integration->type !== IntegrationType::UiTPAS) {
            return;
        }

        $mailTemplate = new MailTemplate(
            $templateName,
            $integration->type
        );

        $contacts = $integration->filterUniqueContactsWithPreferredContactType(ContactType::Technical);
        foreach ($contacts as $contact) {
            $context = [
                'firstName' => $contact->firstName,
                'integrationName' => $integration->name,
                'url' => $this->urlGenerator->route('nl.integrations.show', $integration->id),
            ];

            if ($organizerName !== null) {
                $context['organizerName'] = $organizerName;
            }

            $this->mailer->send(
                $this->from,
                new Address($contact->email, trim($contact->firstName . ' ' . $contact->lastName)),
                $mailTemplate,
                $context
            );
        }
    }
}
