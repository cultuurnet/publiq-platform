<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Integrations\Exceptions\InconsistentIntegrationType;
use App\Domain\Integrations\Integration;
use App\Domain\Integrations\IntegrationType;
use App\Domain\Integrations\UdbOrganizers;
use App\Mails\Template\TemplateName;
use App\Pagination\PaginatedCollection;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Ramsey\Uuid\UuidInterface;

interface IntegrationRepository
{
    /**
     * @throws InconsistentIntegrationType
     */
    public function save(Integration $integration): void;
    /**
     * @throws InconsistentIntegrationType
     */
    public function saveWithCoupon(Integration $integration, string $couponCode): void;
    public function update(Integration $integration): void;
    public function getById(UuidInterface $id): Integration;
    public function getByIdWithTrashed(UuidInterface $id): Integration;
    public function deleteById(UuidInterface $id): ?bool;
    public function getByContactEmail(string $email, ?string $searchQuery): PaginatedCollection;
    public function requestActivation(UuidInterface $id, UuidInterface $organizationId, ?string $couponCode, UdbOrganizers $organizers = null): void;
    public function activate(UuidInterface $id): void;
    public function activateWithOrganization(UuidInterface $id, UuidInterface $organizationId, ?string $couponCode, UdbOrganizers $organizers = null): void;
    public function approve(UuidInterface $id): void;

    /** @return Collection<Integration> */
    public function getDraftsByTypeAndBetweenMonthsOld(IntegrationType $type, int $startMonths, int $endMonths, TemplateName $templateName): Collection;
    public function updateReminderEmailSent(UuidInterface $id, TemplateName $templateName, Carbon $date): void;
}
