<?php

declare(strict_types=1);

namespace App\UiTPAS;

use App\Api\TokenStrategy\ClientCredentials;
use App\Domain\Integrations\Events\UdbOrganizerCreated;
use App\Domain\Integrations\GetIntegrationOrganizersWithTestOrganizer;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Integrations\Repositories\UdbOrganizerRepository;
use App\Keycloak\Events\ClientCreated;
use App\Keycloak\Repositories\KeycloakClientRepository;
use App\Mails\Smtp\MailTemplateResolver;
use App\Mails\Smtp\SmtpMailer;
use App\Notifications\MessageBuilder;
use App\Notifications\Slack\SlackNotifier;
use App\Search\Sapi3\SearchService;
use App\Search\UdbOrganizerNameResolver;
use App\UiTPAS\Event\UdbOrganizerApproved;
use App\UiTPAS\Event\UdbOrganizerRejected;
use App\UiTPAS\Listeners\AddUiTPASPermissionsToOrganizerForIntegration;
use App\UiTPAS\Listeners\NotifyUdbOrganizerRequested;
use App\UiTPAS\Listeners\SendMailForUdbOrganizer;
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
                ClientCredentialsContextFactory::getUitIdTestContext()
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
                $this->app->get(UdbOrganizerRepository::class),
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
            $smtp = config('mail.mailers.smtp');

            return new SmtpMailer(
                new SymfonyMailer(
                    Transport::fromDsn(sprintf('smtp://%s:%s@%s:%d', urlencode($smtp['username']), urlencode($smtp['password']), $smtp['host'], $smtp['port']))
                ),
                $this->app->get(MailTemplateResolver::class),
                $this->app->get(LoggerInterface::class),
            );
        });

        $this->app->singleton(SendMailForUdbOrganizer::class, function () {
            return new SendMailForUdbOrganizer(
                $this->app->get(SmtpMailer::class),
                $this->app->get(UdbOrganizerRepository::class),
                $this->app->get(IntegrationRepository::class),
                $this->app->get(UdbOrganizerNameResolver::class),
                $this->app->get(SearchService::class),
                $this->app->get(UrlGenerator::class),
                new Address(config('mail.from.address'), config('mail.from.name')),
            );
        });

        $this->bootstrapEventHandling();
    }

    private function bootstrapEventHandling(): void
    {
        if (!config(UiTPASConfig::AUTOMATIC_PERMISSIONS_ENABLED->value)) {
            return;
        }

        Event::listen(ClientCreated::class, [AddUiTPASPermissionsToOrganizerForIntegration::class, 'handle']);
        Event::listen(UdbOrganizerCreated::class, [NotifyUdbOrganizerRequested::class, 'handle']);

        Event::listen(UdbOrganizerCreated::class, [SendMailForUdbOrganizer::class, 'handleUdbOrganizerCreated']);
        Event::listen(UdbOrganizerApproved::class, [SendMailForUdbOrganizer::class, 'handleUdbOrganizerApproved']);
        Event::listen(UdbOrganizerRejected::class, [SendMailForUdbOrganizer::class, 'handleUdbOrganizerRejected']);
    }
}
