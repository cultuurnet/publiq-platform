<?php

declare(strict_types=1);

namespace App\Auth0\Models;

use App\Auth0\Events;
use App\Auth0\Auth0Tenant;
use App\Domain\Contacts\Models\ContactModel;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Models\UuidModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

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
    ];

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
     * @return BelongsTo<IntegrationModel, ContactModel>
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(IntegrationModel::class, 'integration_id');
    }

    public function block()
    {
        $a = 1;
        die('stop');
    }
}
