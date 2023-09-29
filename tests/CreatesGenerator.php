<?php

declare(strict_types=1);

namespace Tests;

trait CreatesGenerator
{
    private function createGenerator(array $items): \Generator
    {
        $i = 0;

        while ($i < count($items)) {
            yield $items[$i];
            $i++;
        }
    }
}
