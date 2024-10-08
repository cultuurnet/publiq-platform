<?php

declare(strict_types=1);

namespace App\Console\Commands\Migrations;

use App\Domain\Contacts\ContactType;

final class ContactCsvRow
{
    private array $contactAsArray;

    public function __construct(array $contactAsArray)
    {
        $this->contactAsArray = array_map(
            fn (string $value) => $value !== 'NULL' ? $value : null,
            $contactAsArray
        );
    }

    public function insightlyContactId(): int
    {
        return (int) $this->insightlyId(0);
    }

    public function insightlyOpportunityId(): ?int
    {
        return $this->insightlyId(2);
    }

    public function insightlyProjectId(): ?int
    {
        return $this->insightlyId(3);
    }

    public function firstName(): string
    {
        return $this->contactAsArray[4];
    }

    public function lastName(): string
    {
        return $this->contactAsArray[5];
    }

    public function email(): string
    {
        return $this->contactAsArray[8];
    }

    public function contactType(): ContactType
    {
        return ContactType::from(mb_strtolower($this->contactAsArray[9]));
    }

    private function insightlyId(int $index): ?int
    {
        if ($this->contactAsArray[$index] === null) {
            return null;
        }

        return (int) $this->contactAsArray[$index];
    }
}
