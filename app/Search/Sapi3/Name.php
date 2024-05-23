<?php

declare(strict_types=1);

namespace App\Search\Sapi3;

use CultuurNet\SearchV3\Parameter\AbstractParameter;

final class Name extends AbstractParameter
{
    public function __construct(string $name)
    {
        $this->value = $name;
        $this->key = 'name';
    }
}
