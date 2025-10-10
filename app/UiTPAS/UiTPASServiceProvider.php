<?php

declare(strict_types=1);

namespace App\UiTPAS;

use App\Api\TokenStrategy\ClientCredentials;
use App\Domain\Integrations\Events\IntegrationActivationRequested;
use App\Domain\Integrations\GetIntegrationOrganizersWithTestOrganizer;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Mail\Mailer;
use App\Keycloak\Events\ClientCreated;
use App\Keycloak\Repositories\KeycloakClientRepository;
use App\Mails\Smtp\MailTemplateResolver;
use App\Mails\Smtp\SmtpMailer;
use App\Notifications\MessageBuilder;
use App\Notifications\Slack\SlackNotifier;
use App\Search\Sapi3\SearchService;
use App\Search\UdbOrganizerNameResolver;
use App\UiTPAS\Event\UdbOrganizerApproved;
use App\UiTPAS\Event\UdbOrganizerDeleted;
use App\UiTPAS\Event\UdbOrganizerRejected;
use App\UiTPAS\Event\UdbOrganizerRequested;
use App\UiTPAS\Listeners\AddUiTPASPermissionsToOrganizerForIntegration;
use App\UiTPAS\Listeners\NotifyUdbOrganizerRequested;
use App\UiTPAS\Listeners\RevokeUiTPASPermissions;
use App\UiTPAS\Listeners\SendUiTPASMails;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Address;

final class UiTPASServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(UiTPASApiInterface::class, function () {
            return new UiTPASApi(
                new Client([RequestOptions::HTTP_ERRORS => false]),
                new ClientCredentials(
                    new Client([RequestOptions::HTTP_ERRORS => false]),
                    $this->app->get(LoggerInterface::class),
                ),
                $this->app->get(LoggerInterface::class),
                (string)config(UiTPASConfig::TEST_API_ENDPOINT->value),
                (string)config(UiTPASConfig::PROD_API_ENDPOINT->value),
            );
        });

        $this->app->singleton(AddUiTPASPermissionsToOrganizerForIntegration::class, function () {
            return new AddUiTPASPermissionsToOrganizerForIntegration(
                $this->app->get(IntegrationRepository::class),
                $this->app->get(KeycloakClientRepository::class),
                $this->app->get(UiTPASApiInterface::class),
                ClientCredentialsContextFactory::getUitIdTestContext(),
                ClientCredentialsContextFactory::getUitIdProdContext(),
                $this->app->get(LoggerInterface::class),
            );
        });

        $this->app->singleton(GetIntegrationOrganizersWithTestOrganizer::class, function () {
            return new GetIntegrationOrganizersWithTestOrganizer(
                $this->app->get(SearchService::class),
                $this->app->get(UiTPASApiInterface::class),
                ClientCredentialsContextFactory::getUitIdTestContext(),
                ClientCredentialsContextFactory::getUitIdProdContext(),
            );
        });

        $this->app->singleton(NotifyUdbOrganizerRequested::class, function () {
            return new NotifyUdbOrganizerRequested(
                $this->app->get(IntegrationRepository::class),
                new SlackNotifier(
                    config('slack.botToken'),
                    config('slack.channels.uitpas_integraties'),
                    config('slack.baseUri')
                ),
                $this->app->get(MessageBuilder::class),
                $this->app->get(LoggerInterface::class),
            );
        });

        $this->app->singleton(SmtpMailer::class, function () {
            return new SmtpMailer(
                new SymfonyMailer(
                    Transport::fromDsn(config('mail.mailers.smtp.dsn'))
                ),
                $this->app->get(MailTemplateResolver::class),
                $this->app->get(LoggerInterface::class),
            );
        });

        $this->app->singleton(SendUiTPASMails::class, function () {
            return new SendUiTPASMails(
                $this->app->get(Mailer::class),
                $this->app->get(IntegrationRepository::class),
                $this->app->get(UdbOrganizerNameResolver::class),
                $this->app->get(SearchService::class),
                $this->app->get(UrlGenerator::class),
                new Address(config('mail.from.address'), config('mail.from.name')),
            );
        });

        $this->app->singleton(RevokeUiTPASPermissions::class, function () {
            return new RevokeUiTPASPermissions(
                $this->app->get(IntegrationRepository::class),
                $this->app->get(UiTPASApiInterface::class),
                ClientCredentialsContextFactory::getUitIdProdContext(),
                $this->app->get(LoggerInterface::class),
            );
        });

        $this->bootstrapEventHandling();
    }

    private function bootstrapEventHandling(): void
    {
        Event::listen(ClientCreated::class, [AddUiTPASPermissionsToOrganizerForIntegration::class, 'handleCreateTestPermissions']);
        Event::listen(UdbOrganizerApproved::class, [AddUiTPASPermissionsToOrganizerForIntegration::class, 'handleCreateProductionPermissions']);
        Event::listen(UdbOrganizerDeleted::class, [RevokeUiTPASPermissions::class, 'handle']);

        Event::listen(UdbOrganizerRequested::class, [NotifyUdbOrganizerRequested::class, 'handleUdbOrganizerRequested']);
        Event::listen(IntegrationActivationRequested::class, [NotifyUdbOrganizerRequested::class, 'handleIntegrationActivationRequested']);

        Event::listen(IntegrationActivationRequested::class, [SendUiTPASMails::class, 'handleIntegrationActivationRequested']);
        Event::listen(UdbOrganizerRequested::class, [SendUiTPASMails::class, 'handleUdbOrganizerRequested']);
        Event::listen(UdbOrganizerApproved::class, [SendUiTPASMails::class, 'handleUdbOrganizerApproved']);
        Event::listen(UdbOrganizerRejected::class, [SendUiTPASMails::class, 'handleUdbOrganizerRejected']);
    }
}
