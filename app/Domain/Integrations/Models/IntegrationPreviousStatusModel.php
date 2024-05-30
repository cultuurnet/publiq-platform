<?php

declare(strict_types=1);

namespace App\Domain\Integrations\Models;

use App\Models\UuidModel;

/**
 * @property string $status
 */
final class IntegrationPreviousStatusModel extends UuidModel
{
    protected $table = 'integrations_previous_statuses';

    protected $fillable = [
        'id',
        'status',
    ];
}
