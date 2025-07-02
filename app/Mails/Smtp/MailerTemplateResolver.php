<?php

declare(strict_types=1);

namespace App\Mails\Smtp;

interface MailerTemplateResolver
{
    public function getSubject(MailerTemplate $mailerTemplate, array $variables = []): string;

    public function render(MailerTemplate $mailerTemplate, array $variables = []): string;
}
