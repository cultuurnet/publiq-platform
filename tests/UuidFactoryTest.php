<?php

declare(strict_types=1);

namespace Tests;

use DateTimeInterface;
use Ramsey\Uuid\Type\Hexadecimal;
use Ramsey\Uuid\Type\Integer as IntegerObject;
use Ramsey\Uuid\UuidFactory;
use Ramsey\Uuid\UuidFactoryInterface;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Validator\ValidatorInterface;

final class UuidFactoryTest implements UuidFactoryInterface
{
    public function __construct(readonly UuidInterface $testUuid)
    {
    }

    public function fromBytes(string $bytes): UuidInterface
    {
        return $this->testUuid;
    }

    public function fromDateTime(DateTimeInterface $dateTime, ?Hexadecimal $node = null, ?int $clockSeq = null): UuidInterface
    {
        return $this->testUuid;
    }

    public function fromInteger(string $integer): UuidInterface
    {
        return $this->testUuid;
    }

    public function fromString(string $uuid): UuidInterface
    {
        return $this->testUuid;
    }

    public function getValidator(): ValidatorInterface
    {
        return (new UuidFactory())->getValidator();
    }

    public function uuid1($node = null, ?int $clockSeq = null): UuidInterface
    {
        return $this->testUuid;
    }

    public function uuid2(int $localDomain, ?IntegerObject $localIdentifier = null, ?Hexadecimal $node = null, ?int $clockSeq = null): UuidInterface
    {
        return $this->testUuid;
    }

    public function uuid3($ns, string $name): UuidInterface
    {
        return $this->testUuid;
    }

    public function uuid4(): UuidInterface
    {
        return $this->testUuid;
    }

    public function uuid5($ns, string $name): UuidInterface
    {
        return $this->testUuid;
    }

    public function uuid6(?Hexadecimal $node = null, ?int $clockSeq = null): UuidInterface
    {
        return $this->testUuid;
    }
}
