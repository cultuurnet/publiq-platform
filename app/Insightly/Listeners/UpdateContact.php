<?php

declare(strict_types=1);

namespace App\Insightly\Listeners;

use App\Domain\Contacts\Events\ContactUpdated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;
use Throwable;

final class UpdateContact implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(ContactUpdated $contactUpdated): void
    {
        $this->logger->info(
            'Contact updated',
            [
                'domain' => 'insightly',
                'contact_id' => $contactUpdated->id->toString(),
            ]
        );
    }

    public function failed(ContactUpdated $contactUpdated, Throwable $exception): void
    {
        $this->logger->error(
            'Failed to update contact',
            [
                'domain' => 'insightly',
                'contact_id' => $contactUpdated->id->toString(),
                'exception' => $exception,
            ]
        );
    }
}
