<?php

declare(strict_types=1);

namespace App\Domain\Histories\Models;

use App\Domain\Histories\History;
use App\Models\UuidModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    /**
     * @return BelongsTo<UuidModel, HistoryModel>
     */
    public function integration(): BelongsTo
    {
        return $this->belongsTo(UuidModel::class, 'domain_id');
    }

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
