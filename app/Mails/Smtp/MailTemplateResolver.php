<?php

declare(strict_types=1);

namespace App\Mails\Smtp;

use App\Mails\Template\MailTemplate;

interface MailTemplateResolver
{
    public function getSubject(MailTemplate $mailerTemplate, array $variables = []): string;

    public function render(MailTemplate $mailerTemplate, array $variables = []): string;
}
