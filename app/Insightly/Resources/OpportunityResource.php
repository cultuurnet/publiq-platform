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

    public function get(int $id): array;

    public function delete(int $id): void;

    public function update(int $id, Integration $integration): void;

    public function updateStage(int $id, OpportunityStage $stage): void;

    public function updateState(int $id, OpportunityState $state): void;

    public function linkContact(int $id, int $contactId, ContactType $contactType): void;

    /**
     * @throws ContactCannotBeUnlinked
     */
    public function unlinkContact(int $id, int $contactId): void;
}
