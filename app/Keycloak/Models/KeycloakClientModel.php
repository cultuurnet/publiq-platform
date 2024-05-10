<?php

namespace App\Keycloak\Models;

use App\Keycloak\Client;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Keycloak\RealmCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class KeycloakClientModel extends Model
{
    use HasFactory;

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
        return new Client(
            Uuid::fromString($this->id),
            Uuid::fromString($this->integration_id),
            $this->client_id,
            $this->client_secret,
            RealmCollection::from($this->realm)
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
