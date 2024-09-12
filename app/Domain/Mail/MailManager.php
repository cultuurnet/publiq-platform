<?php

declare(strict_types=1);

namespace App\Domain\Mail;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Events\IntegrationActivated;
use App\Domain\Integrations\Events\IntegrationBlocked;
use App\Domain\Integrations\Events\IntegrationCreatedWithContacts;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use Symfony\Component\Mime\Address;

final class MailManager
{
    private const SUBJECT_INTEGRATION_ACTIVATED = 'Publiq platform - Integration activated';
    private const SUBJECT_INTEGRATION_BLOCKED = 'Publiq platform - Integration blocked';
    private const SUBJECT_INTEGRATION_CREATED = 'Welcome to Publiq platform - Let\'s get you started!';

    public function __construct(
        private readonly Mailer $mailer,
        private readonly IntegrationRepository $integrationRepository,
        private readonly int $templateIntegrationCreated,
        private readonly int $templateIntegrationActivated,
        private readonly int $templateIntegrationBlocked,
        private readonly string $baseUrl
    ) {
    }

    public function sendIntegrationCreatedMail(IntegrationCreatedWithContacts $integrationCreated): void
    {
        $integration = $this->integrationRepository->getById($integrationCreated->id);

        // The technical contact get  additional information in the e-mail (example a link to the satisfaction survey), so this type of contact gets preference when matching email addresses are found
        foreach ($this->getUniqueContactsWithPreferredContactType($integration, ContactType::Technical) as $contact) {
            $this->mailer->send(
                $this->getFrom(),
                $this->getAddresses($contact),
                $this->templateIntegrationCreated,
                self::SUBJECT_INTEGRATION_CREATED,
                $this->getIntegrationVariables($contact, $integration)
            );
        }
    }

    public function sendIntegrationActivatedMail(IntegrationActivated $integrationActivated): void
    {
        $integration = $this->integrationRepository->getById($integrationActivated->id);

        foreach ($this->getUniqueContactsWithPreferredContactType($integration, ContactType::Technical) as $contact) {
            $this->mailer->send(
                $this->getFrom(),
                $this->getAddresses($contact),
                $this->templateIntegrationActivated,
                self::SUBJECT_INTEGRATION_ACTIVATED,
                $this->getIntegrationVariables($contact, $integration)
            );
        }
    }

    public function sendIntegrationBlockedMail(IntegrationBlocked $integrationBlocked): void
    {
        $integration = $this->integrationRepository->getById($integrationBlocked->id);

        foreach ($this->getUniqueContactsWithPreferredContactType($integration, ContactType::Technical) as $contact) {
            $this->mailer->send(
                $this->getFrom(),
                $this->getAddresses($contact),
                $this->templateIntegrationBlocked,
                self::SUBJECT_INTEGRATION_BLOCKED,
                [
                    'firstName' => $contact->firstName,
                    'lastName' => $contact->lastName,
                    'contactType' => $contact->type->value,
                    'integrationName' => $integration->name,
                ]
            );
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
}
