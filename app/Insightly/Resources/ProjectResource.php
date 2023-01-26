<?php

declare(strict_types=1);

namespace App\Insightly\Resources;

use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Integration;
use App\Insightly\Objects\ProjectStage;
use App\Insightly\Objects\ProjectState;

interface ProjectResource
{
    public function create(Integration $integration): int;

    public function updateWithCoupon(int $projectId, string $couponCode): void;

    public function delete(int $projectId): void;

    public function updateStage(int $projectId, ProjectStage $stage): void;

    public function updateState(int $projectId, ProjectState $state): void;

    public function linkOpportunity(int $projectId, int $opportunityId): void;

    public function linkContact(int $projectId, int $contactId, ContactType $contactType): void;
}
