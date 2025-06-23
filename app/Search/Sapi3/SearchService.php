<?php

declare(strict_types=1);

namespace App\Search\Sapi3;

use App\Domain\Udb3Uuid;
use CultuurNet\SearchV3\ValueObjects\PagedCollection;

interface SearchService
{
    public function searchUiTPASOrganizer(string $name): PagedCollection;
    public function findUiTPASOrganizers(Udb3Uuid ...$ids): PagedCollection;
}
