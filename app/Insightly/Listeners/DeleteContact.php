<?php

declare(strict_types=1);

namespace App\Insightly\Listeners;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Psr\Log\LoggerInterface;

final class DeleteContact implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function handle(): void
    {
        $this->logger->info('deleting');
    }

}
