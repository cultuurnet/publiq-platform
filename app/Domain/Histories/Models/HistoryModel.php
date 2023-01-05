<?php

declare(strict_types=1);

namespace App\Domain\Histories\Models;

use App\Models\UuidModel;

final class HistoryModel extends UuidModel
{
    protected $table = 'histories';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'item_id',
        'user_id',
        'type',
        'action',
        'timestamp',
    ];
}
