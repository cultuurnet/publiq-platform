<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Integrations\IntegrationUrl;
use Illuminate\Support\Collection;
use Ramsey\Uuid\UuidInterface;

interface IntegrationUrlRepository
{
    public function save(IntegrationUrl $integrationUrl): void;
    public function update(IntegrationUrl $integrationUrl): void;
    /**
    * @param Collection<IntegrationUrl> $integrationUrls
     */
    public function updateUrls(Collection $integrationUrls): void;
    public function getById(UuidInterface $id): IntegrationUrl;

    /**
     * @return Collection<IntegrationUrl>
     */
    public function getByIntegrationId(UuidInterface $integrationId): Collection;
    /**
    * @param array<UuidInterface> $ids
    * @return array<IntegrationUrl>
     */
    public function getByIds(array $ids): array;
    public function deleteById(UuidInterface $id): void;
    /**
     * @param Collection<UuidInterface> $ids
     */
    public function deleteByIds(Collection $ids): void;
}
