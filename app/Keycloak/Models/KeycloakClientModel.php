<?php

declare(strict_types=1);

namespace App\Keycloak\Models;

use App\Keycloak\Client;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Keycloak\RealmCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

final class KeycloakClientModel extends Model
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
            // Trying to use magic getters for eg $this->id gives a 0 back
            Uuid::fromString($this->attributes['id']),
            Uuid::fromString($this->attributes['integration_id']),
            Uuid::fromString($this->attributes['client_id']),
            $this->client_secret,
            RealmCollection::fromInternalName($this->realm)
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
