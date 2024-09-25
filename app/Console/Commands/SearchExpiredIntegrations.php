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
    protected $signature = 'integration:search-expired-integrations {--force : Skip confirmation prompt}';

    protected $description = 'Search for expired integrations and dispatch event';

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly LoggerInterface $logger,
        private readonly array $expirationTimers
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $integrations = new Collection();
        foreach ($this->expirationTimers as $integrationType => $expirationTimer) {
            $integrations = $integrations->merge(
                $this->integrationRepository->getDraftsByTypeAndBetweenMonthsOld(
                    IntegrationType::from($integrationType),
                    $expirationTimer,
                    self::ONE_YEAR,
                    TemplateName::INTEGRATION_ACTIVATION_REMINDER->value
                )
            );
        }

        if ($integrations->isEmpty()) {
            $this->output->writeln('No expired integrations');
            return self::SUCCESS;
        }

        if (!$this->option('force')
            && !$this->confirm(sprintf('Are you sure you want to dispatch expirations for %d integrations?', count($integrations)))) {
            return self::SUCCESS;
        }

        foreach ($integrations as $integration) {
            ActivationExpired::dispatch($integration->id);

            $msg = sprintf('Dispatched ActivationExpired for integration %s', $integration->id);
            $this->output->writeln($msg);
            $this->logger->info($msg);
        }

        return self::SUCCESS;
    }
}
