<?php

declare(strict_types=1);

namespace App\Insightly\Resources;

use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Integration;
use App\Insightly\Exceptions\ContactCannotBeUnlinked;
use App\Insightly\Objects\OpportunityStage;
use App\Insightly\Objects\OpportunityState;

interface OpportunityResource
{
    public function create(Integration $integration): int;

    public function delete(int $id): void;

    public function updateStage(int $id, OpportunityStage $stage): void;

    public function updateState(Integration $integration, int $id, OpportunityState $state): void;

    public function linkContact(int $opportunityId, int $contactId, ContactType $contactType): void;

    /**
     * @throws ContactCannotBeUnlinked
     */
    public function unlinkContact(int $opportunityId, int $contactId): void;
}
