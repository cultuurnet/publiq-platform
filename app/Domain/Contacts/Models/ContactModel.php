<?php

declare(strict_types=1);

namespace App\Domain\Contacts\Models;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Events\ContactCreated;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Insightly\Models\InsightlyMappingModel;
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

    /**
     * @return BelongsTo<InsightlyMappingModel, ContactModel>
     */
    public function insightlyMapping(): BelongsTo
    {
        return $this->belongsTo(InsightlyMappingModel::class, 'id');
    }

    public function insightlyId(): ?string
    {
        return $this->insightlyMapping ? $this->insightlyMapping->insightly_id : null;
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
