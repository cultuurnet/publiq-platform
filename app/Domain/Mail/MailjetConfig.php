<?php

declare(strict_types=1);

namespace App\Domain\Mail;

final class MailjetConfig
{
    // This has a longer convoluted name because there is also a mailinglist integration with mailjet that is unrelated.
    public const TRANSACTIONAL_EMAILS_ENABLED = 'mailjet.enabled';
    public const API_KEY = 'mailjet.api.key';
    public const API_SECRET = 'mailjet.api.secret';
    public const TEMPLATE_INTEGRATION_BLOCKED = 'mailjet.templates.integration_blocked';
    public const TEMPLATE_INTEGRATION_ACTIVATED = 'mailjet.templates.integration_activated';
}
