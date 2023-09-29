<?php

declare(strict_types=1);

namespace Tests;

use Generator;
use Ramsey\Uuid\FeatureSet;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidInterface;

final class UuidTestFactory extends UuidFactory
{
    use CreatesGenerator;
    private Generator $idGenerator;

    public function __construct(array $uuids, ?FeatureSet $features = null)
    {
        parent::__construct($features);
        $this->idGenerator = $this->createGenerator($uuids);
    }

    public function uuid4(): UuidInterface
    {
        $current = $this->idGenerator->current();
        var_dump($current);
        $this->idGenerator->next();

        if ($current !== null) {
            return Uuid::fromString($current);
        }

        return parent::uuid4();
    }
}
