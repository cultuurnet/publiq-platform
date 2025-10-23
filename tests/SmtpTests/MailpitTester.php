<?php

declare(strict_types=1);

namespace Tests\SmtpTests;

use Illuminate\Support\Facades\Http;

trait MailpitTester
{
    private const TIMEOUT_SECONDS_MAIL = 5;

    private function waitForMail(callable $matcher): ?array
    {
        $start = time();

        while ((time() - $start) < self::TIMEOUT_SECONDS_MAIL) {
            $response = Http::get(config('mail.mailers.mailpit.api_url') . '/api/v1/messages');

            if (!$response->successful()) {
                usleep(250);
                continue;
            }

            $messages = $response->json('messages');
            foreach ($messages as $message) {
                if ($matcher($message)) {
                    return $message;
                }
            }
        }

        return null;
    }
}
