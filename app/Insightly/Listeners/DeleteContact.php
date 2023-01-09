<?php

declare(strict_types=1);

namespace App\Insightly\Listeners;

use App\Domain\Contacts\Events\ContactDeleted;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Insightly\InsightlyClient;
use App\Insightly\Repositories\InsightlyMappingRepository;
use App\Insightly\SyncIsAllowed;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;
use Throwable;

final class DeleteContact implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly InsightlyClient $insightlyClient,
        private readonly ContactRepository $contactRepository,
        private readonly InsightlyMappingRepository $insightlyMappingRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(ContactDeleted $contactDeleted): void
    {
        $contact = $this->contactRepository->getById($contactDeleted->id);
        if (!SyncIsAllowed::forContact($contact)) {
            return;
        }

        $insightlyMapping = $this->insightlyMappingRepository->getById($contact->id);

        $this->insightlyClient->contacts()->delete($insightlyMapping->insightlyId);

        $this->logger->info(
            'Contact deleted',
            [
                'domain' => 'insightly',
                'contact_id' => $contactDeleted->id->toString(),
            ]
        );
    }

    public function failed(ContactDeleted $contactDeleted, Throwable $exception): void
    {
        $this->logger->error(
            'Failed to delete contact',
            [
                'domain' => 'insightly',
                'contact_id' => $contactDeleted->id->toString(),
                'exception' => $exception,
            ]
        );
    }
}
