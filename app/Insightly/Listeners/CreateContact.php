<?php

declare(strict_types=1);

namespace App\Insightly\Listeners;

use App\Domain\Contacts\Events\ContactCreated;
use App\Domain\Contacts\Repositories\ContactRepository;
use App\Insightly\InsightlyClient;
use Illuminate\Contracts\Queue\ShouldQueue;

final class CreateContact implements ShouldQueue
{
    public function __construct(
        private readonly InsightlyClient $insightlyClient,
        private readonly ContactRepository $contactRepository
    ) {
    }

    public function handle(ContactCreated $contactCreated): void
    {
        if (empty(config('insightly.api_key'))) {
            return;
        }

        $this->insightlyClient->contacts()->create(
            $this->contactRepository->getById($contactCreated->id)
        );
    }
}
