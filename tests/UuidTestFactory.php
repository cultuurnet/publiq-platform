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

    private Generator $uuid4Generator;

    /**
     * @param array<string, array<string>> $uuids
     */
    public function __construct(array $uuids, ?FeatureSet $features = null)
    {
        parent::__construct($features);
        $this->uuid4Generator = $this->createGenerator($uuids['uuid4']);
    }

    public function uuid4(): UuidInterface
    {
        $current = $this->uuid4Generator->current();
        $this->uuid4Generator->next();

        if ($current !== null) {
            return Uuid::fromString($current);
        }

        return parent::uuid4();
    }
}
