<?php

declare(strict_types=1);

namespace App\Mails;

use App\Domain\Integrations\Events\IntegrationActivated;
use App\Domain\Integrations\Events\IntegrationBlocked;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Mail\Mailer;
use App\Domain\Mail\MailjetConfig;
use App\Domain\Mail\MailjetMailer;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Mailjet\Client;
use Psr\Log\LoggerInterface;

final class MailServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (!config(MailjetConfig::TRANSACTIONAL_EMAILS_ENABLED)) {
            return;
        }

        $this->app->singleton(Mailer::class, function () {
            return new MailjetMailer(
                new Client(
                    config(MailjetConfig::API_KEY),
                    config(MailjetConfig::API_SECRET),
                    true,
                    ['version' => 'v3.1']
                ),
                $this->app->get(LoggerInterface::class)
            );
        });


        $this->app->singleton(MailManager::class, function () {
            return new MailManager(
                $this->app->get(Mailer::class),
                $this->app->get(IntegrationRepository::class),
                (int)config(MailjetConfig::TEMPLATE_INTEGRATION_ACTIVATED),
                (int)config(MailjetConfig::TEMPLATE_INTEGRATION_BLOCKED),
                env('APP_URL')
            );
        });

        Event::listen(IntegrationActivated::class, [MailManager::class, 'sendIntegrationActivatedMail']);
        Event::listen(IntegrationBlocked::class, [MailManager::class, 'sendIntegrationBlockedMail']);
    }
}
