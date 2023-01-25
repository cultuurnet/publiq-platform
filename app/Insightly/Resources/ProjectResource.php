<?php

declare(strict_types=1);

namespace App\Insightly\Resources;

use App\Domain\Contacts\ContactType;
use App\Domain\Integrations\Integration;
use App\Insightly\Objects\ProjectStage;

interface ProjectResource
{
    public function create(Integration $integration): int;

    public function delete(int $id): void;

    public function updateStage(int $id, ProjectStage $stage): void;

    public function linkOpportunity(int $projectId, int $opportunityId): void;

    public function linkContact(int $projectId, int $contactId, ContactType $contactType): void;
}
