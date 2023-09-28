<?php

declare(strict_types=1);

namespace Tests;

use DateTimeInterface;
use Ramsey\Uuid\Type\Hexadecimal;
use Ramsey\Uuid\Type\Integer as IntegerObject;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidFactoryInterface;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Validator\ValidatorInterface;

final class UuidTestFactory implements UuidFactoryInterface
{
    /**
     * @var array<string, array<string>> $uuids
     */
    private array $uuids;
    private int $uuid4Index;
    private UuidFactoryInterface $defaultFactory;

    /**
     * @param array<string, array<string>> $uuids
     * @param UuidFactoryInterface|null $factory
     */
    public function __construct(array $uuids, ?UuidFactoryInterface $factory = null) {
        $this->uuids = $uuids;
        $this->uuid4Index = 0;
        $this->defaultFactory = $factory ?? new UuidFactory();
    }


    public function fromBytes(string $bytes): UuidInterface
    {
        return $this->defaultFactory->fromBytes($bytes);
    }

    public function fromDateTime(DateTimeInterface $dateTime, ?Hexadecimal $node = null, ?int $clockSeq = null): UuidInterface
    {
        return $this->defaultFactory->fromDateTime($dateTime, $node, $clockSeq);
    }

    public function fromInteger(string $integer): UuidInterface
    {
        return $this->defaultFactory->fromInteger($integer);
    }

    public function fromString(string $uuid): UuidInterface
    {
        return $this->defaultFactory->fromString($uuid);
    }

    public function getValidator(): ValidatorInterface
    {
        return $this->defaultFactory->getValidator();
    }

    public function uuid1($node = null, ?int $clockSeq = null): UuidInterface
    {
        return $this->defaultFactory->uuid1($node, $clockSeq);
    }

    public function uuid2(int $localDomain, ?IntegerObject $localIdentifier = null, ?Hexadecimal $node = null, ?int $clockSeq = null): UuidInterface
    {
        return $this->defaultFactory->uuid2($localDomain, $localIdentifier, $node, $clockSeq);
    }

    public function uuid3($ns, string $name): UuidInterface
    {
        return $this->defaultFactory->uuid3($ns, $name);
    }

    public function uuid4(): UuidInterface
    {
        if (isset($this->uuids['uuid4'][$this->uuid4Index])) {
            $uuid = $this->uuids['uuid4'][$this->uuid4Index];
            $this->uuid4Index += 1;
            return Uuid::fromString($uuid);
        }

        return $this->defaultFactory->uuid4();
    }

    public function uuid5($ns, string $name): UuidInterface
    {
        return $this->defaultFactory->uuid5($ns, $name);
    }

    public function uuid6(?Hexadecimal $node = null, ?int $clockSeq = null): UuidInterface
    {
        return $this->defaultFactory->uuid6($node, $clockSeq);
    }
}
