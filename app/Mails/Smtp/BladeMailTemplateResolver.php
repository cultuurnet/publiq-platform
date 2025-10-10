<?php

declare(strict_types=1);

namespace App\Mails\Smtp;

use App\Mails\Template\MailTemplate;
use Illuminate\Contracts\View\Factory as ViewFactory;

final readonly class BladeMailTemplateResolver implements MailTemplateResolver
{
    private const TEMPLATE_ROOT = 'mails';

    public function __construct(
        private ViewFactory $view,
    ) {

    }

    public function getSubject(MailTemplate $mailerTemplate, array $variables = []): string
    {
        return $this->renderSubjectString($mailerTemplate->getSubject(), $variables);
    }

    public function render(MailTemplate $mailerTemplate, array $variables = []): string
    {
        $specificTemplate = implode('.', [self::TEMPLATE_ROOT, $mailerTemplate->type->value,  $mailerTemplate->name->value]);
        $genericTemplate = implode('.', [self::TEMPLATE_ROOT, $mailerTemplate->name->value]);

        if ($this->view->exists($specificTemplate)) {
            return $this->view->make($specificTemplate, $variables)->render();
        }

        return $this->view->make($genericTemplate, $variables)->render();
    }

    private function renderSubjectString(string $subject, array $variables): string
    {
        return preg_replace_callback('/{{\s*\$([a-zA-Z_][a-zA-Z0-9_]*)\s*}}/', static function ($matches) use ($variables) {
            return $variables[$matches[1]] ?? $matches[0];
        }, $subject) ?? '';
    }
}
