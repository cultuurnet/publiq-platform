<?php

declare(strict_types=1);

namespace App\Domain\Contacts\Models;

use App\Domain\Contacts\Contact;
use App\Domain\Contacts\ContactType;
use App\Domain\Contacts\Events\ContactCreated;
use App\Domain\Contacts\Events\ContactUpdated;
use App\Domain\Integrations\Models\IntegrationModel;
use App\Insightly\Models\InsightlyMappingModel;
use App\Models\UuidModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

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
        self::created(
            static fn (ContactModel $contactModel) => ContactCreated::dispatch($contactModel->getId())
        );
        self::updated(
            static fn (ContactModel $contactModel) => ContactUpdated::dispatch($contactModel->getId())
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
            $this->getId(),
            $this->getIntegrationId(),
            $this->email,
            $this->getType(),
            $this->first_name,
            $this->last_name
        );
    }

    public function getId(): UuidInterface
    {
        return Uuid::fromString($this->id);
    }

    public function getIntegrationId(): UuidInterface
    {
        return Uuid::fromString($this->integration_id);
    }

    public function getType(): ContactType
    {
        return ContactType::from($this->type);
    }
}
