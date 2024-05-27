<?php

declare(strict_types=1);

namespace App\Search;

use CultuurNet\SearchV3\ValueObjects\PagedCollection;

interface SearchService
{
    public function searchUiTPASOrganizer(string $name): PagedCollection;
}
