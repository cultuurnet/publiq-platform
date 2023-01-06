<?php

declare(strict_types=1);

namespace App\Domain\Histories\Repositories;

use App\Domain\Histories\History;
use App\Domain\Histories\Models\HistoryModel;

final class EloquentHistoryRepository implements HistoryRepository
{
    public function create(History $history): void
    {
        HistoryModel::query()->create([
            'id' => $history->id,
            'item_id' => $history->itemId,
            'user_id' => $history->userId,
            'type' => $history->type,
            'action' => $history->action,
            'timestamp' => $history->timestamp,
        ]);
    }
}
