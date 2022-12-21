<?php

declare(strict_types=1);

namespace App\Insightly\Interfaces;

use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Integration;
use App\Insightly\Objects\OpportunityStage;

interface OpportunityResource
{
    public function create(Integration $integration): int;

    public function delete(int $id): void;

    public function updateStage(int $id, OpportunityStage $stage): void;

    public function linkContact(int $opportunityId, int $contactId, ContactType $contactType): void;
}
