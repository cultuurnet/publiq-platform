<?php

declare(strict_types=1);

namespace App\Domain\Histories\Repositories;

use App\Domain\Histories\History;

final class EloquentHistoryRepository implements HistoryRepository
{
    public function create(History $history): void
    {
    }
}
