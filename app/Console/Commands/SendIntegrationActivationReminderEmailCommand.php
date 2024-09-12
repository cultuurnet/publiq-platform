<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Mails\MailJet\MailjetConfig;
use App\Mails\SendIntegrationActivationReminderEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

final class SendIntegrationActivationReminderEmailCommand extends Command
{
    protected $signature = 'cronjob:send-activation-reminder-email {--force : Skip confirmation prompt}';

    protected $description = 'Send activation reminder email';

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly SendIntegrationActivationReminderEmail $sendIntegrationActivationReminderEmail,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if (!config(MailjetConfig::TRANSACTIONAL_EMAILS_ENABLED)) {
            $this->output->writeln('Email feature flag is disabled - mails not sent');
            return self::FAILURE;
        }

        $integrationTypeConfig = [
            IntegrationType::SearchApi->value => 6,
            IntegrationType::Widgets->value => 3,
        ];

        $integrations = new Collection();
        foreach ($integrationTypeConfig as $integrationType => $months) {
            $integrations->merge(
                $this->integrationRepository->getIntegrationsThatHaveNotBeenActivatedYetByType(
                    IntegrationType::from($integrationType),
                    $months
                )
            );
        }

        if ($integrations->isEmpty()) {
            $this->output->writeln('No integrations found to sent reminder emails');
            return self::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm(sprintf('Are you sure you want to sent reminder emails for %d integrations?', count($integrations)))) {
            return self::SUCCESS;
        }

        $this->sendIntegrationActivationReminderEmail->send($integrations, $this->output);

        return self::SUCCESS;
    }
}
