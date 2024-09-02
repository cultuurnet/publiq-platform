<?php

declare(strict_types=1);

namespace App\Domain\Mail;

use App\Domain\Contacts\Contact;
use App\Domain\Integrations\Events\IntegrationActivated;
use App\Domain\Integrations\Events\IntegrationBlocked;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use Symfony\Component\Mime\Address;

final class MailManager
{
    private const SUBJECT_INTEGRATION_ACTIVATED = 'Publiq platform - Integration activated';
    private const SUBJECT_INTEGRATION_BLOCKED = 'Publiq platform - Integration blocked';

    public function __construct(
        private readonly Mailer $mailer,
        private readonly IntegrationRepository $integrationRepository,
        private readonly int $templateIntegrationActivated,
        private readonly int $templateIntegrationBlocked,
        private readonly string $baseUrl
    ) {
    }

    public function sendIntegrationActivatedMail(IntegrationActivated $integrationActivated): void
    {
        $integration = $this->integrationRepository->getById($integrationActivated->id);

        foreach ($integration->contacts() as $contact) {
            $this->mailer->send(
                $this->getFrom(),
                $this->getAddresses($contact),
                $this->templateIntegrationActivated,
                self::SUBJECT_INTEGRATION_ACTIVATED,
                [
                    'firstName' => $contact->firstName,
                    'lastName' => $contact->lastName,
                    'contactType' => $contact->type->value,
                    'integrationName' => $integration->name,
                    'url' => $this->baseUrl . '/nl/integraties/' . $integration->id,
                    'type' => $integration->type->value,
                ]
            );
        }
    }

    public function sendIntegrationBlockedMail(IntegrationBlocked $integrationBlocked): void
    {
        $integration = $this->integrationRepository->getById($integrationBlocked->id);

        foreach ($integration->contacts() as $contact) {

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
}
