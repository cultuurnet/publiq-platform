<?php

declare(strict_types=1);

namespace App\Auth0\Models;

use App\Auth0\Auth0Client;
use App\Auth0\Auth0Tenant;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Models\UuidModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;
use Spatie\Activitylog\LogOptions;

final class Auth0ClientModel extends UuidModel
{
    use SoftDeletes;

    protected $table = 'auth0_clients';

    protected $fillable = [
        'id',
        'integration_id',
        'auth0_client_id',
        'auth0_client_secret',
        'auth0_tenant',
        'distributed_at',
    ];

    protected $casts = [
        'distributed_at' => 'datetime',
    ];

    public function isDistributed(): bool
    {
        return $this->distributed_at !== null;
    }

    public function toDomain(): Auth0Client
    {
        return new Auth0Client(
            Uuid::fromString($this->id),
            Uuid::fromString($this->integration_id),
            $this->auth0_client_id,
            $this->auth0_client_secret,
            Auth0Tenant::from($this->auth0_tenant)
        );
    }

    /**
     * @return BelongsTo<IntegrationModel, Auth0ClientModel>
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(IntegrationModel::class, 'integration_id');
    }
}
