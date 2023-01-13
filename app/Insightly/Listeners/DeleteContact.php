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
        $contact = $this->contactRepository->getDeletedById($contactDeleted->id);
        if (!SyncIsAllowed::forContact($contact)) {
            return;
        }

        $contactInsightlyId = $this->insightlyMappingRepository->getById($contact->id)->insightlyId;
        $integrationInsightlyId = $this->insightlyMappingRepository->getById($contact->integrationId)->insightlyId;

        $this->insightlyClient->opportunities()->unlinkContact($integrationInsightlyId, $contactInsightlyId);

        $this->logger->info(
            'Contact unlinked from opportunity.',
            [
                'domain' => 'insightly',
                'contact_id' => $contactDeleted->id->toString(),
            ]
        );
    }

    public function failed(ContactDeleted $contactDeleted, Throwable $exception): void
    {
        $this->logger->error(
            'Failed to unlink contact from opportunity.',
            [
                'domain' => 'insightly',
                'contact_id' => $contactDeleted->id->toString(),
                'exception' => $exception,
            ]
        );
    }
}
