<?php

declare(strict_types=1);

namespace App\Domain\Mail;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Events\ActivationExpired;
use App\Domain\Integrations\Events\IntegrationActivated;
use App\Domain\Integrations\Events\IntegrationActivationRequested;
use App\Domain\Integrations\Events\IntegrationApproved;
use App\Domain\Integrations\Events\IntegrationCreatedWithContacts;
use App\Domain\Integrations\Events\IntegrationDeleted;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationMail;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationMailRepository;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Mails\Template\MailTemplate;
use App\Mails\Template\TemplateName;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Mime\Address;

final class MailManager implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Mailer $mailer,
        private readonly IntegrationRepository $integrationRepository,
        private readonly IntegrationMailRepository $integrationMailRepository,
        private readonly string $baseUrl,
        private readonly string $fromAddress,
        private readonly string $fromName,
    ) {
    }

    public function handleIntegrationCreatedWithContacts(IntegrationCreatedWithContacts $event): void
    {
        $integration = $this->integrationRepository->getById($event->id);

        $this->sendMail($integration, new MailTemplate(
            TemplateName::INTEGRATION_CREATED,
            $integration->type
        ));
    }

    public function handleIntegrationActivated(IntegrationActivated $event): void
    {
        $integration = $this->integrationRepository->getById($event->id);

        if ($integration->type === IntegrationType::UiTPAS) {
            return;
        }

        $this->sendMail($integration, new MailTemplate(
            TemplateName::INTEGRATION_ACTIVATED,
            $integration->type
        ));
    }

    public function handleIntegrationApproved(IntegrationApproved $event): void
    {
        $integration = $this->integrationRepository->getById($event->id);

        // Currently the same e-mail as integration activated
        $this->sendMail($integration, new MailTemplate(
            TemplateName::INTEGRATION_ACTIVATED,
            $integration->type
        ));
    }

    public function handleIntegrationActivationRequested(IntegrationActivationRequested $event): void
    {
        $integration = $this->integrationRepository->getById($event->id);

        $this->sendMail($integration, new MailTemplate(
            TemplateName::INTEGRATION_ACTIVATION_REQUEST,
            $integration->type
        ));
    }

    public function handleIntegrationDeleted(IntegrationDeleted $event): void
    {
        $integration = $this->integrationRepository->getByIdWithTrashed($event->id);

        $this->sendMail($integration, new MailTemplate(
            TemplateName::INTEGRATION_DELETED,
            $integration->type
        ));
    }

    public function handleActivationExpired(ActivationExpired $event): void
    {
        $integration = $this->integrationRepository->getById($event->id);

        $this->sendMail($integration, new MailTemplate(
            $event->templateName,
            $integration->type
        ));
    }

    private function getFrom(): Address
    {
        return new Address($this->fromAddress, $this->fromName);
    }

    private function getIntegrationVariables(Contact $contact, Integration $integration): array
    {
        return [
            'firstName' => $contact->firstName,
            'lastName' => $contact->lastName,
            'contactType' => $contact->type->value,
            'integrationName' => $integration->name,
            'url' => $this->baseUrl . '/nl/integraties/' . $integration->id,
            'type' => $integration->type->value,
        ];
    }

    private function sendMail(Integration $integration, MailTemplate $template): void
    {
        // The technical contact get  additional information in the e-mail (example: a link to the satisfaction survey), so this type of contact gets preference when matching email addresses are found
        foreach ($integration->filterUniqueContactsWithPreferredContactType(ContactType::Technical) as $contact) {
            $this->mailer->send(
                $this->getFrom(),
                new Address($contact->email, trim($contact->firstName . ' ' . $contact->lastName)),
                $template,
                $this->getIntegrationVariables($contact, $integration)
            );
        }

        $this->integrationMailRepository->create(new IntegrationMail(
            Uuid::uuid4(),
            $integration->id,
            $template->name,
        ));
    }
}
