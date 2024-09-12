<?php

declare(strict_types=1);

namespace App\Mails;

use App\Domain\Contacts\Contact;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\Repositories\IntegrationRepository;
use App\Domain\Mail\MailManager;
use Carbon\Carbon;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

final readonly class SendIntegrationActivationReminderEmail
{
    public function __construct(
        private MailManager $mailManager,
        private IntegrationRepository $integrationRepository,
        private LoggerInterface $logger
    ) {
    }

    public function send(Collection $integrations, OutputStyle $output): void
    {
        foreach ($integrations as $integration) {
            $this->mailManager->sendActivationReminderEmail($integration);

            $this->integrationRepository->update($integration->withreminderEmailSent(Carbon::now()));

            $msg = sprintf('Sending activation reminder about integration %s to %s', $integration->id, $this->getEmails($integration));

            $this->logger->info($msg);

            $output->writeln($msg);
        }
    }

    private function getEmails(Integration $integration): string
    {
        return implode(', ', array_unique(array_map(static function (Contact $contact) {
            return $contact->email;
        }, $integration->contacts())));
    }
}
