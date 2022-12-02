<?php

declare(strict_types=1);

namespace App\Domain\Contacts\Models;

use App\Domain\Contacts\Events\ContactCreated;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Models\UuidModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

final class ContactModel extends UuidModel
{
    use SoftDeletes;

    protected $table = 'contacts';

    protected $fillable = [
        'id',
        'integration_id',
        'type',
        'first_name',
        'last_name',
        'email',
    ];

    protected static function booted(): void
    {
        self::created(
            static fn ($contactModel) => ContactCreated::dispatch(Uuid::fromString($contactModel->id))
        );
    }

    /**
     * @return BelongsTo<IntegrationModel, ContactModel>
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(IntegrationModel::class, 'integration_id');
    }
}
