<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Integrations\Events\ActivationExpired;
use App\Domain\Integrations\IntegrationExpirationTime;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

final class SearchExpiredIntegrations extends Command
{
    protected $signature = 'integration:search-expired-integrations {--force : Skip confirmation prompt}';

    protected $description = 'Search for expired integrations and dispatch event';

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $integrations = new Collection();
        foreach (IntegrationExpirationTime::cases() as $expirationTimeConfig) {
            $integrations = $integrations->merge(
                $this->integrationRepository->getDraftsByTypeAndOlderThenMonthsAgo(
                    IntegrationType::fromName($expirationTimeConfig->name),
                    $expirationTimeConfig->value
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
