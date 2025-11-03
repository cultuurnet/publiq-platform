<?php

declare(strict_types=1);

namespace App\Mails\Template;

use App\Domain\Integrations\IntegrationType;

final readonly class MailTemplate
{
    public function __construct(public TemplateName $name, public IntegrationType $type)
    {
    }

    public function getSubject(): string
    {
        return match ($this->name) {
            TemplateName::INTEGRATION_CREATED => 'Je integratie {{ $integrationName }} is succesvol aangemaakt!',
            TemplateName::INTEGRATION_ACTIVATED => 'Je integratie {{ $integrationName }} is geactiveerd!',
            TemplateName::INTEGRATION_ACTIVATION_REQUEST => 'Activatieaanvraag met integratie {{ $integrationName }}',
            TemplateName::INTEGRATION_DELETED => 'Integratie {{ $integrationName }} is definitief verwijderd',

            // Cron jobs
            TemplateName::INTEGRATION_ACTIVATION_REMINDER => 'Hulp nodig met je Integratie {{ $integrationName }}?',
            TemplateName::INTEGRATION_FINAL_ACTIVATION_REMINDER => 'Je integratie {{ $integrationName }} wordt binnenkort verwijderd',

            // UiTPAS specific
            TemplateName::ORGANISATION_UITPAS_REQUESTED => 'Activatieaanvraag met integratie {{ $integrationName }} voor {{ $organizerName }}!',
            TemplateName::ORGANISATION_UITPAS_APPROVED => 'Je integratie {{ $integrationName }} voor {{ $organizerName }} is geactiveerd!',
            TemplateName::ORGANISATION_UITPAS_REJECTED => 'Je integratie {{ $integrationName }} voor {{ $organizerName }} is afgekeurd!',
        };
    }
}
