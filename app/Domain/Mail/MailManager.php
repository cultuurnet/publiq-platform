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
use App\Domain\Integrations\Repositories\IntegrationMailRepository;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Mails\Template\Template;
use App\Mails\Template\TemplateName;
use App\Mails\Template\Templates;
use Illuminate\Bus\Queueable;
use Symfony\Component\Mime\Address;

final class MailManager
{
    use Queueable;

    public function __construct(
        private readonly Mailer $mailer,
        private readonly IntegrationRepository $integrationRepository,
        private readonly IntegrationMailRepository $integrationMailRepository,
        private readonly Templates $templates,
        private readonly string $baseUrl
    ) {
    }

    public function sendIntegrationCreatedMail(IntegrationCreatedWithContacts $event): void
    {
        $integration = $this->integrationRepository->getById($event->id);

        $this->sendMail($integration, $this->templates->getOrFail(TemplateName::INTEGRATION_CREATED->value));
    }

    public function sendIntegrationActivatedMail(IntegrationActivated $event): void
    {
        $integration = $this->integrationRepository->getById($event->id);

        $this->sendMail($integration, $this->templates->getOrFail(TemplateName::INTEGRATION_ACTIVATED->value));
    }

    public function sendIntegrationApprovedMail(IntegrationApproved $event): void
    {
        $integration = $this->integrationRepository->getById($event->id);

        // Currently the same e-mail as integration activated
        $this->sendMail($integration, $this->templates->getOrFail(TemplateName::INTEGRATION_ACTIVATED->value));
    }

    public function sendIntegrationActivationRequestMail(IntegrationActivationRequested $event): void
    {
        $integration = $this->integrationRepository->getById($event->id);

        $this->sendMail($integration, $this->templates->getOrFail(TemplateName::INTEGRATION_ACTIVATION_REQUEST->value));
    }

    public function sendIntegrationDeletedMail(IntegrationDeleted $event): void
    {
        $integration = $this->integrationRepository->getByIdWithTrashed($event->id);

        $this->sendMail($integration, $this->templates->getOrFail(TemplateName::INTEGRATION_DELETED->value));
    }

    public function sendActivationReminderEmail(ActivationExpired $event): void
    {
        $integration = $this->integrationRepository->getById($event->id);

        $this->sendMail($integration, $this->templates->getOrFail($event->templateName->value));
    }

    private function getFrom(): Address
    {
        return new Address(config('mail.from.address'), config('mail.from.name'));
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

    /**
     * To optimize email credits and prevent spamming we check that the same email is not sent multiple times to the same e-mail address
     * @return Contact[]
     */
    private function getUniqueContactsWithPreferredContactType(Integration $integration, ContactType $contactType): array
    {
        $uniqueContacts = [];

        foreach ($integration->contacts() as $contact) {
            if (!isset($uniqueContacts[$contact->email]) || $contact->type === $contactType) {
                $uniqueContacts[$contact->email] = $contact;
            }
        }

        return $uniqueContacts;
    }

    public function sendMail(Integration $integration, Template $template): void
    {
        if (!$template->enabled) {
            return;
        }

        // The technical contact get  additional information in the e-mail (example a link to the satisfaction survey), so this type of contact gets preference when matching email addresses are found
        foreach ($this->getUniqueContactsWithPreferredContactType($integration, ContactType::Technical) as $contact) {
            $this->mailer->send(
                $this->getFrom(),
                new Address($contact->email, trim($contact->firstName . ' ' . $contact->lastName)),
                $template->id,
                $this->getIntegrationVariables($contact, $integration)
            );
        }

        $this->integrationMailRepository->create(new IntegrationMail(
            $integration->id,
            $template->type,
        ));
    }
}
