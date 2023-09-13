<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Repositories;

use App\Domain\Integrations\FormRequests\UpdateIntegrationUrlRequest;
use App\Domain\Integrations\IntegrationUrl;
use App\Domain\Integrations\Models\IntegrationUrlModel;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\UuidInterface;

final class EloquentIntegrationUrlRepository implements IntegrationUrlRepository
{
    public function save(IntegrationUrl $integrationUrl): void
    {
        IntegrationUrlModel::query()->create([
            'id' => $integrationUrl->id,
            'integration_id' => $integrationUrl->integrationId,
            'type' => $integrationUrl->type,
            'environment' => $integrationUrl->environment,
            'url' => $integrationUrl->url,
         ]);
    }

    public function update(UpdateIntegrationUrlRequest $request): void
    {
        foreach (['loginUrls', 'callbackUrls', 'logoutUrls'] as $property) {
            $this->updateUrls($request->input($property) ?? []);
        }
    }

    public function getById(UuidInterface $id): IntegrationUrl
    {
        /**
         * @var IntegrationUrlModel $integrationUrlModel
         */
        $integrationUrlModel = IntegrationUrlModel::query()->findOrFail($id->toString());
        return $integrationUrlModel->toDomain();
    }

    public function deleteById(UuidInterface $id): ?bool
    {
        /**
         * @var IntegrationUrlModel $integrationUrlModel
         */
        $integrationUrlModel = IntegrationUrlModel::query()->findOrFail($id->toString());
        return $integrationUrlModel->delete();
    }

    private function updateUrls(array $urls): void
    {
        if (count($urls) === 0) {
            return;
        }

        DB::transaction(static function () use ($urls) {
            foreach ($urls as $url) {
                /** @var IntegrationUrlModel $integrationUrlModel */
                $integrationUrlModel = IntegrationUrlModel::query()->findOrFail($url['id']);
                $integrationUrlModel['url'] = $url['url'];
                $integrationUrlModel->save();
            }
        });
    }
}
