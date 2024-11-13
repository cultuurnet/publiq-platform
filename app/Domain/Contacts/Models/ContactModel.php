<?php

declare(strict_types=1);

namespace App\Domain\Contacts\Models;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Events\ContactCreated;
use App\Domain\Contacts\Events\ContactDeleted;
use App\Domain\Contacts\Events\ContactUpdated;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Insightly\Models\InsightlyMappingModel;
use App\Models\UuidModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

/**
 * @property string $id
 * @property string $integration_id
 * @property string $type
 * @property string $email
 * @property string $first_name
 * @property string $last_name
 */
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
        self::creating(
            static fn (ContactModel $contactModel) => ContactModel::onlyTrashed()->where('email', $contactModel->email)->where('integration_id', $contactModel->integration_id)->first()?->forceDelete()
        );
        self::created(
            static fn (ContactModel $contactModel) => ContactCreated::dispatch($contactModel->toDomain()->id)
        );
        self::updated(
            static fn (ContactModel $contactModel) => ContactUpdated::dispatch($contactModel->toDomain()->id, $contactModel->isDirty(['email']))
        );
        self::deleted(
            static fn (ContactModel $contactModel) => ContactDeleted::dispatch($contactModel->toDomain()->id)
        );
    }

    /**
     * @return BelongsTo<IntegrationModel, $this>
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(IntegrationModel::class, 'integration_id');
    }

    /**
     * @return HasMany<InsightlyMappingModel, $this>
     */
    public function insightlyMappings(): HasMany
    {
        return $this->hasMany(InsightlyMappingModel::class, 'id');
    }

    public function insightlyId(): ?string
    {
        return $this->insightlyMappings()->first()->insightly_id ?? null;
    }

    public function toDomain(): Contact
    {
        return new Contact(
            Uuid::fromString($this->id),
            Uuid::fromString($this->integration_id),
            $this->email,
            ContactType::from($this->type),
            $this->first_name,
            $this->last_name
        );
    }
}
