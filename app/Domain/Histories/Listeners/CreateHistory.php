<?php

declare(strict_types=1);

namespace App\Domain\Histories\Listeners;

use App\Domain\Auth\CurrentUser;
use App\Domain\Histories\History;
use App\Domain\Histories\Repositories\HistoryRepository;
use Illuminate\Support\Carbon;
use Ramsey\Uuid\Uuid;

final class CreateHistory
{
    public function __construct(
        private readonly HistoryRepository $historyRepository,
        private readonly CurrentUser $currentUser
    ) {
    }

    public function handle(string $eventName, array $data): void
    {
        $this->historyRepository->create(
            new History(
                Uuid::uuid4(),
                $data[0]->id,
                $eventName,
                $this->currentUser->id(),
                Carbon::now(),
                $eventName
            )
        );
    }
}
