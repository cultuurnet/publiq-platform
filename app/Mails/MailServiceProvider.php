<?php

declare(strict_types=1);

namespace App\Mails;

use App\Domain\Integrations\Events\ActivationExpired;
use App\Domain\Integrations\Events\IntegrationActivated;
use App\Domain\Integrations\Events\IntegrationActivationRequested;
use App\Domain\Integrations\Events\IntegrationApproved;
use App\Domain\Integrations\Events\IntegrationCreatedWithContacts;
use App\Domain\Integrations\Events\IntegrationDeleted;
use App\Domain\Integrations\Repositories\IntegrationMailRepository;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Mail\Mailer;
use App\Domain\Mail\MailManager;
use App\Mails\MailJet\MailjetConfig;
use App\Mails\Smtp\BladeMailTemplateResolver;
use App\Mails\Smtp\MailTemplateResolver;
use App\Mails\Smtp\SmtpMailer;
use App\Mails\Template\Templates;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mailer\Transport;

final class MailServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(MailTemplateResolver::class, function () {
            return $this->app->get(BladeMailTemplateResolver::class);
        });

        $this->app->singleton(Mailer::class, function () {
            return new SmtpMailer(
                new SymfonyMailer(
                    Transport::fromDsn(config('mail.mailers.smtp.dsn'))
                ),
                $this->app->get(MailTemplateResolver::class),
                $this->app->get(LoggerInterface::class),
            );
        });

        $this->app->singleton(MailManager::class, function () {
            return new MailManager(
                $this->app->get(Mailer::class),
                $this->app->get(IntegrationRepository::class),
                $this->app->get(IntegrationMailRepository::class),
                Templates::build(config(MailjetConfig::MAILJET_TEMPLATES)),
                config('app.url'),
            );
        });

        Event::listen(IntegrationCreatedWithContacts::class, [MailManager::class, 'sendIntegrationCreatedMail']);
        Event::listen(IntegrationActivated::class, [MailManager::class, 'sendIntegrationActivatedMail']);
        Event::listen(IntegrationApproved::class, [MailManager::class, 'sendIntegrationApprovedMail']);
        Event::listen(ActivationExpired::class, [MailManager::class, 'sendActivationReminderEmail']);
        Event::listen(IntegrationActivationRequested::class, [MailManager::class, 'sendIntegrationActivationRequestMail']);
        Event::listen(IntegrationDeleted::class, [MailManager::class, 'sendIntegrationDeletedMail']);
    }
}
