<?php

declare(strict_types=1);

namespace App\Mails\MailJet;

final class MailjetConfig
{
    // This has a longer convoluted name because there is also a mailinglist integration with mailjet that is unrelated.
    public const TRANSACTIONAL_EMAILS_ENABLED = 'mailjet.enabled';
    public const API_KEY = 'mailjet.api.key';
    public const API_SECRET = 'mailjet.api.secret';
    public const SANDBOX_MODE = 'mailjet.sandbox_mode';
    public const SANDBOX_ALLOWED_DOMAINS = 'mailjet.sandbox_allowed_domains';
    public const MAILJET_TEMPLATES = 'mailjet.templates';
    public const MAILJET_EXPIRATION_TIMERS = 'mailjet.expiration_timers';
    public const MAILJET_EXPIRATION_TIMERS_FINAL_REMINDER = 'mailjet.expiration_timers_final_reminder';
}
