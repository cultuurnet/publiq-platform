<?php

declare(strict_types=1);

namespace App\Mails\Smtp;

use Illuminate\Contracts\View\Factory as ViewFactory;

final readonly class BladeMailTemplateResolver implements MailTemplateResolver
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
            MailTemplate::INTEGRATION_CREATED->value => 'integration.uitpas.created',
            MailTemplate::ORGANISATION_UITPAS_REQUESTED->value => 'integration.uitpas.requested',
            MailTemplate::INTEGRATION_ACTIVATED->value => 'integration.activated',
            MailTemplate::ORGANISATION_UITPAS_APPROVED->value => 'integration.uitpas.approved',
            MailTemplate::ORGANISATION_UITPAS_REJECTED->value => 'integration.uitpas.rejected',
        ];
        $this->subjects = [
            MailTemplate::INTEGRATION_CREATED->value => 'Je integratie {{ $integrationName }} is succesvol aangemaakt!',
            MailTemplate::ORGANISATION_UITPAS_REQUESTED->value => 'Activatieaanvraag met integratie {{ $integrationName }} voor {{ $organizerName }}',
            MailTemplate::INTEGRATION_ACTIVATED->value => 'Je integratie {{ $integrationName }} is geactiveerd!',
            MailTemplate::ORGANISATION_UITPAS_APPROVED->value => 'Je integratie {{ $integrationName }} voor {{ $organizerName }} is geactiveerd',
            MailTemplate::ORGANISATION_UITPAS_REJECTED->value => 'Je integratie {{ $integrationName }} voor {{ $organizerName }} is afgekeurd',
        ];
    }

    public function getSubject(MailTemplate $mailerTemplate, array $variables = []): string
    {
        return $this->renderSubjectString($this->subjects[$mailerTemplate->value], $variables);
    }

    public function render(MailTemplate $mailerTemplate, array $variables = []): string
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
