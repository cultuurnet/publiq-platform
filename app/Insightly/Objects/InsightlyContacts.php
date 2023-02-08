<?php

declare(strict_types=1);

namespace App\Insightly\Objects;

use Illuminate\Support\Collection;

final class InsightlyContacts extends Collection
{
    public function mostLinks(): InsightlyContact
    {
        usort($this->items, static function (InsightlyContact $a, InsightlyContact $b): int {
            if ($a->numberOfLinks === $b->numberOfLinks) {
                return $a->insightlyId <=> $b->insightlyId;
            }

            return $b->numberOfLinks <=> $a->numberOfLinks;
        });

        return $this->items[0];
    }
}
