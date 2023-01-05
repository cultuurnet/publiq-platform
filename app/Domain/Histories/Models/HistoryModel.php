<?php

declare(strict_types=1);

namespace App\Domain\Histories\Models;

use App\Domain\Histories\History;
use App\Models\UuidModel;
use Illuminate\Support\Carbon;
use Ramsey\Uuid\Uuid;

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

    public function toDomain(): History
    {
        return new History(
            Uuid::fromString($this->id),
            Uuid::fromString($this->itemId),
            $this->userId,
            $this->type,
            $this->action,
            (new Carbon())->rawParse($this->timestamp)
        );

    }
}
