<?php

declare(strict_types=1);

namespace App\Insightly\Resources;

use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Integration;
use App\Insightly\Exceptions\ContactCannotBeUnlinked;
use App\Insightly\Objects\ProjectStage;
use App\Insightly\Objects\ProjectState;

interface ProjectResource
{
    public function create(Integration $integration): int;

    public function get(int $id): array;

    public function updateWithCoupon(int $id, string $couponCode): void;

    public function delete(int $id): void;

    public function update(int $id, Integration $integration): void;

    public function updateStage(int $id, ProjectStage $stage): void;

    public function updateState(int $id, ProjectState $state): void;

    public function linkOpportunity(int $id, int $opportunityId): void;

    public function linkContact(int $id, int $contactId, ContactType $contactType): void;

    public function linkOrganization(int $id, int $organizationId): void;

    /**
     * @throws ContactCannotBeUnlinked
     */
    public function unlinkContact(int $id, int $contactId): void;
}
