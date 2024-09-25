<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Integrations\Events\ActivationExpired;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Mails\Template\TemplateName;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

final class SearchExpiredIntegrations extends Command
{
    private const ONE_YEAR = 12;
    private const ONE_CENTURY = 12000; // Just a very long time to fetch all integrations older than 1 year

    protected $signature = 'integration:search-expired-integrations
                        {--force : Skip confirmation prompt}
                        {--only-first-warning : Only sent the first warning}
                        {--only-final-warning : Only sent the final warning}';

    protected $description = 'Search for expired integrations and dispatch event';

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly LoggerInterface $logger,
        private readonly array $expirationTimers,
        private readonly array $expirationTimersFinalWarning
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $sendFirstWarnings = $this->option('only-first-warning');
        $sendFinalWarning = $this->option('only-final-warning');

        if (!$sendFirstWarnings && !$sendFinalWarning) {
            $sendFirstWarnings = true;
            $sendFinalWarning = true;
        }

        $integrations = $sendFirstWarnings ?
            $this->buildIntegrationsCollection(
                $this->expirationTimers,
                self::ONE_YEAR,
                TemplateName::INTEGRATION_ACTIVATION_REMINDER
            ) :
            new Collection();

        $integrationsFinalWarning = $sendFinalWarning ?
            $this->buildIntegrationsCollection(
                $this->expirationTimersFinalWarning,
                self::ONE_CENTURY,
                TemplateName::INTEGRATION_FINAL_ACTIVATION_REMINDER
            ) :
            new Collection();

        if ($integrations->isEmpty() && $integrationsFinalWarning->isEmpty()) {
            $this->output->writeln('No expired integrations');
            return self::SUCCESS;
        }

        if (!$this->option('force')
            && !$this->confirm(sprintf('Are you sure you want to dispatch expirations for %d integrations?', count($integrations) + count($integrationsFinalWarning)))) {
            return self::SUCCESS;
        }

        $this->sendExpirationMessages($integrations, TemplateName::INTEGRATION_ACTIVATION_REMINDER);
        $this->sendExpirationMessages($integrationsFinalWarning, TemplateName::INTEGRATION_FINAL_ACTIVATION_REMINDER);

        return self::SUCCESS;
    }

    private function buildIntegrationsCollection(array $expirationTimers, int $endDate, TemplateName $templateName): Collection
    {
        $integrations = new Collection();
        foreach ($expirationTimers as $integrationType => $expirationTimer) {
            $integrations = $integrations->merge(
                $this->integrationRepository->getDraftsByTypeAndBetweenMonthsOld(
                    IntegrationType::from($integrationType),
                    $expirationTimer,
                    $endDate,
                    $templateName->value
                )
            );
        }

        return $integrations;
    }

    private function sendExpirationMessages(Collection $integrations, TemplateName $templateName): void
    {
        foreach ($integrations as $integration) {
            ActivationExpired::dispatch($integration->id, $templateName->value);

            $msg = sprintf('Dispatched ActivationExpired for integration %s', $integration->id);
            $this->output->writeln($msg);
            $this->logger->info($msg);
        }
    }
}
