<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Models;

use App\Models\UuidModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AdminInformationModel extends UuidModel
{
    protected $table = 'admin_information';

    protected $fillable = [
        'id',
        'integration_id',
        'on_hold',
        'comment',
    ];

    /**
     * @return BelongsTo<IntegrationModel, $this>
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(IntegrationModel::class, 'integration_id');
    }
}
