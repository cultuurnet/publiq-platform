<?php

declare(strict_types=1);

namespace App\Domain\Histories\Repositories;

use App\Domain\Histories\History;

interface HistoryRepository
{
    public function create(History $history): void;
}
