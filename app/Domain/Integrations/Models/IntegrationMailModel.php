<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class IntegrationMailModel extends Model
{
    protected $table = 'integrations_mails';

    protected $fillable = [
        'uuid',
        'integration_id',
        'template_name',
    ];

    /**
     * @return BelongsTo<IntegrationModel, IntegrationMailModel>
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(IntegrationModel::class, 'integration_id');
    }
}
