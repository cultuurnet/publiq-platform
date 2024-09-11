<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domain\Contacts\Contact;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Mail\MailManager;
use App\Mails\MailJet\MailjetConfig;
use Carbon\Carbon;
use Illuminate\Console\Command;

final class SendIntegrationActivationReminderEmail extends Command
{
    protected $signature = 'cronjob:send-activation-reminder-email {--force : Skip confirmation prompt}';

    protected $description = 'Send activation reminder email';

    public function __construct(
        private readonly IntegrationRepository $integrationRepository,
        private readonly MailManager $mailManager,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if (!config(MailjetConfig::TRANSACTIONAL_EMAILS_ENABLED)) {
            $this->output->writeln('Email feature flag is disabled - mails not sent');
            return self::FAILURE;
        }

        $integrations = $this->integrationRepository->getIntegrationsThatHaveNotBeenActivatedYet();

        if ($integrations->isEmpty()) {
            $this->output->writeln('No integrations found to sent reminder emails');
            return self::SUCCESS;
        }

        if (!$this->option('force') && !$this->confirm(sprintf('Are you sure you want to sent reminder emails for %d integrations?', count($integrations)))) {
            return self::SUCCESS;
        }

        foreach ($integrations as $integration) {
            $this->mailManager->sendActivationReminderEmail($integration);

            $this->integrationRepository->update($integration->withSentReminderEmail(Carbon::now()));

            $emails = implode(', ', array_unique(array_map(static function (Contact $contact) {
                return $contact->email;
            }, $integration->contacts())));

            $this->output->writeln(sprintf('Sending activation reminder about integration %s to %s', $integration->id, $emails));
        }

        return self::SUCCESS;
    }
}
