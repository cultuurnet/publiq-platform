<?php

declare(strict_types=1);

namespace App\Pagination;

final readonly class PaginationInfo
{
    public function __construct(public array $links, public int $totalItems)
    {
    }
}
