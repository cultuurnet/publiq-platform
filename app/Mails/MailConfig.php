<?php

declare(strict_types=1);

namespace App\Mails;

enum MailConfig : string
{
    case INTEGRATION_EXPIRATION_TIMER = 'mail.timers.expiration_timers';
    case INTEGRATION_EXPIRATION_TIMER_FINAL_REMINDER = 'mail.timers.expiration_timers_final_reminder';

}
