<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Models;

use App\Domain\Contacts\Models\ContactModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

final class IntegrationModel extends Model
{
    use SoftDeletes;

    protected $table = 'integration';

    protected $fillable = [
        'id',
        'type',
        'name',
        'description',
        'subscription_id',
    ];

    /**
     * @return BelongsToMany<ContactModel>
     */
    public function contacts(): BelongsToMany
    {
        return $this->belongsToMany(
            ContactModel::class,
            'integration_contact',
            'integration_id',
            'contact_id',
        );
    }
}
