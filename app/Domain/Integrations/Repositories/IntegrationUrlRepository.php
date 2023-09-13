<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Integrations\FormRequests\UpdateIntegrationUrlRequest;
use App\Domain\Integrations\IntegrationUrl;
use Ramsey\Uuid\UuidInterface;

interface IntegrationUrlRepository
{
    public function save(IntegrationUrl $integrationUrl): void;
    public function update(UpdateIntegrationUrlRequest $request): void;
    public function getById(UuidInterface $id): IntegrationUrl;
    public function deleteById(UuidInterface $id): ?bool;
}
