<?php

declare(strict_types=1);

namespace App\Mails\Smtp;

interface MailerTemplateResolver
{
    public function getSubject(MailTemplate $mailerTemplate, array $variables = []): string;

    public function render(MailTemplate $mailerTemplate, array $variables = []): string;
}
