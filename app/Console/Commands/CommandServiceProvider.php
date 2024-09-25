<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Mails\MailJet\MailjetConfig;
use Illuminate\Support\ServiceProvider;
use Psr\Log\LoggerInterface;

final class CommandServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SearchExpiredIntegrations::class, function () {
            return new SearchExpiredIntegrations(
                $this->app->get(IntegrationRepository::class),
                $this->app->get(LoggerInterface::class),
                config(MailjetConfig::MAILJET_EXPIRATION_TIMERS),
                config(MailjetConfig::MAILJET_EXPIRATION_TIMERS_FINAL_WARNING),
            );
        });
    }
}
