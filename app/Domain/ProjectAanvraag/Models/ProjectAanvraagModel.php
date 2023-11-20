<?php

namespace App\Domain\ProjectAanvraag\Models;

use App\Domain\Integrations\Models\IntegrationModel;
use App\Domain\ProjectAanvraag\ProjectAanvraag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ramsey\Uuid\Uuid;

final class ProjectAanvraagModel extends Model
{
    protected $table = 'projectaanvraag';

    protected $fillable = [
        'integration_id',
        'projectaanvraag_id'
    ];

    /**
     * @return BelongsTo<IntegrationModel, ProjectAanvraagModel>
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(IntegrationModel::class, 'integration_id');
    }

    public function toDomain(): ProjectAanvraag
    {
        return new ProjectAanvraag(
            Uuid::fromString($this->integration_id),
            $this->projectaanvraag_id,
        );
    }
}
