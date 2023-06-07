<?php

declare(strict_types=1);

namespace App\Pagination;

use Illuminate\Support\Collection;

readonly class PaginatedCollection
{
    public function __construct(public Collection $collection, public PaginationInfo $paginationInfo)
    {

    }
}
