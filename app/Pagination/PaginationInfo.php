<?php

declare(strict_types=1);

namespace App\Pagination;

final readonly class PaginationData
{
    public function __construct(public array $links, public int $totalItems)
    {
    }
}
