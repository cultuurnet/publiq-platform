<?php

declare(strict_types=1);

namespace App\Mails;

use App\Domain\Integrations\Events\ActivationExpired;
use App\Domain\Integrations\Events\IntegrationActivated;
use App\Domain\Integrations\Events\IntegrationActivationRequested;
use App\Domain\Integrations\Events\IntegrationBlocked;
use App\Domain\Integrations\Events\IntegrationCreatedWithContacts;
use App\Domain\Integrations\Events\IntegrationDeleted;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Mail\Mailer;
use App\Domain\Mail\MailManager;
use App\Mails\MailJet\MailjetConfig;
use App\Mails\MailJet\MailjetMailer;
use App\Mails\MailJet\SandboxMode;
use App\Mails\Template\Templates;
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
                $this->app->get(LoggerInterface::class),
                new SandboxMode(
                    config(MailjetConfig::SANDBOX_MODE),
                    config(MailjetConfig::SANDBOX_ALLOWED_DOMAINS)
                )
            );
        });

        $this->app->singleton(MailManager::class, function () {
            return new MailManager(
                $this->app->get(Mailer::class),
                $this->app->get(IntegrationRepository::class),
                Templates::build(config(MailjetConfig::MAILJET_TEMPLATES)),
                config('app.url'),
            );
        });

        Event::listen(IntegrationCreatedWithContacts::class, [MailManager::class, 'sendIntegrationCreatedMail']);
        Event::listen(IntegrationActivated::class, [MailManager::class, 'sendIntegrationActivatedMail']);
        Event::listen(IntegrationBlocked::class, [MailManager::class, 'sendIntegrationBlockedMail']);
        Event::listen(ActivationExpired::class, [MailManager::class, 'sendActivationReminderEmail']);
        Event::listen(IntegrationActivationRequested::class, [MailManager::class, 'sendIntegrationActivationRequestMail']);
        Event::listen(IntegrationDeleted::class, [MailManager::class, 'sendIntegrationDeletedMail']);
    }
}
