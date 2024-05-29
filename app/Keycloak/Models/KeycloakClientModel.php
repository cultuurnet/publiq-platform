<?php

declare(strict_types=1);

namespace App\Keycloak\Models;

use App\Keycloak\Client;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Keycloak\Config;
use App\Models\UuidModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;
use Ramsey\Uuid\Uuid;

final class KeycloakClientModel extends UuidModel
{
    use SoftDeletes;

    protected $table = 'keycloak_clients';

    protected $fillable = [
        'id',
        'integration_id',
        'client_id',
        'client_secret',
        'realm',
    ];

    public function toDomain(): Client
    {
        /** @var Config $config */
        $config = App::get(Config::class);

        return new Client(
            Uuid::fromString($this->id),
            Uuid::fromString($this->integration_id),
            Uuid::fromString($this->client_id),
            $this->client_secret,
            $config->realms->fromPublicName($this->realm)
        );
    }

    /**
     * @return BelongsTo<IntegrationModel, KeycloakClientModel>
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(IntegrationModel::class, 'integration_id');
    }
}
