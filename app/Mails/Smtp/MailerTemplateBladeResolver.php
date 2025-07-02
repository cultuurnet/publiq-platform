<?php

declare(strict_types=1);

namespace App\Mails\Smtp;

use Illuminate\Contracts\View\Factory as ViewFactory;

final readonly class MailerTemplateBladeResolver implements MailerTemplateResolver
{
    private const TEMPLATE_ROOT = 'mails.';

    /** @var string[]  */
    private array $templates;

    /** @var string[]  */
    private array $subjects;

    public function __construct(
        private ViewFactory $view,
    ) {
        $this->templates = [
            MailerTemplate::INTEGRATION_ACTIVATED->value => 'integration.activated',
            MailerTemplate::ORGANISATION_UITPAS_REQUESTED->value => 'UdbOrganizer.requested',
            MailerTemplate::ORGANISATION_UITPAS_APPROVED->value => 'UdbOrganizer.approved',
            MailerTemplate::ORGANISATION_UITPAS_REJECTED->value => 'UdbOrganizer.rejected',
        ];
        $this->subjects = [
            MailerTemplate::INTEGRATION_ACTIVATED->value => 'Je integratie {{ $integrationName }} is geactiveerd',
            MailerTemplate::ORGANISATION_UITPAS_REQUESTED->value => 'Activatieaanvraag met integratie {{ $integrationName }} voor {{ $organizerName }}',
            MailerTemplate::ORGANISATION_UITPAS_APPROVED->value => 'Je integratie {{ $integrationName }} voor {{ $organizerName }} is geactiveerd',
            MailerTemplate::ORGANISATION_UITPAS_REJECTED->value => 'Je integratie {{ $integrationName }} voor {{ $organizerName }} is afgekeurd',
        ];
    }

    public function getSubject(MailerTemplate $mailerTemplate, array $variables = []): string
    {
        return $this->renderSubjectString($this->subjects[$mailerTemplate->value], $variables);
    }

    public function render(MailerTemplate $mailerTemplate, array $variables = []): string
    {
        $template = self::TEMPLATE_ROOT . $this->templates[$mailerTemplate->value];

        return $this->view->make($template, $variables)->render();
    }

    private function renderSubjectString(string $subject, array $variables): string
    {
        return preg_replace_callback('/{{\s*\$([a-zA-Z_][a-zA-Z0-9_]*)\s*}}/', static function ($matches) use ($variables) {
            return $variables[$matches[1]] ?? $matches[0];
        }, $subject) ?? '';
    }
}
