<?php

declare(strict_types=1);

namespace App\Domain\Mail;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Events\ActivationExpired;
use App\Domain\Integrations\Events\IntegrationActivated;
use App\Domain\Integrations\Events\IntegrationBlocked;
use App\Domain\Integrations\Events\IntegrationCreatedWithContacts;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Mails\MailEnabled;
use App\Mails\Template\Template;
use App\Mails\Template\Templates;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Symfony\Component\Mime\Address;

final class MailManager
{
    use Queueable;

    public function __construct(
        private readonly Mailer $mailer,
        private readonly IntegrationRepository $integrationRepository,
        private readonly Templates $templates,
        private readonly string $baseUrl
    ) {
    }

    public function sendIntegrationCreatedMail(IntegrationCreatedWithContacts $event): void
    {
        $this->sendMail($event, $this->templates->get(Templates::INTEGRATION_CREATED));
    }

    public function sendIntegrationActivatedMail(IntegrationActivated $event): void
    {
        $this->sendMail($event, $this->templates->get(Templates::INTEGRATION_ACTIVATED));
    }

    public function sendIntegrationBlockedMail(IntegrationBlocked $event): void
    {
        $this->sendMail($event, $this->templates->get(Templates::INTEGRATION_BLOCKED));
    }

    public function sendActivationReminderEmail(ActivationExpired $event): void
    {
        $integration = $this->sendMail($event, $this->templates->get(Templates::INTEGRATION_ACTIVATION_REMINDER));

        if ($integration !== null) {
            $this->integrationRepository->update($integration->withreminderEmailSent(Carbon::now()));
        }
    }

    private function getFrom(): Address
    {
        return new Address(config('mail.from.address'), config('mail.from.name'));
    }

    private function getAddresses(Contact $contact): Addresses
    {
        return new Addresses([new Address($contact->email, trim($contact->firstName . ' ' . $contact->lastName))]);
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

    public function sendMail(MailEnabled $event, ?Template $template): ?Integration
    {
        if ($template === null || !$template->enabled) {
            return null;
        }

        $integration = $this->integrationRepository->getById($event->getId());

        // The technical contact get  additional information in the e-mail (example a link to the satisfaction survey), so this type of contact gets preference when matching email addresses are found
        foreach ($this->getUniqueContactsWithPreferredContactType($integration, ContactType::Technical) as $contact) {
            $this->mailer->send(
                $this->getFrom(),
                $this->getAddresses($contact),
                $template->id,
                $template->subject,
                $this->getIntegrationVariables($contact, $integration)
            );
        }

        return $integration;
    }
}
