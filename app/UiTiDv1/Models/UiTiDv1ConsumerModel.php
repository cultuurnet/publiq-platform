<?php

declare(strict_types=1);

namespace App\UiTiDv1\Models;

use App\UiTiDv1\UiTiDv1Consumer;
use App\UiTiDv1\UiTiDv1Environment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

final class UiTiDv1ConsumerModel extends Model
{
    use SoftDeletes;

    protected $table = 'uitidv1_consumers';
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        'integration_id',
        'consumer_key',
        'consumer_secret',
        'api_key',
        'environment',
    ];

    public function toDomain(): UiTiDv1Consumer
    {
        return new UiTiDv1Consumer(
            Uuid::fromString($this->integration_id),
            $this->consumer_key,
            $this->consumer_secret,
            $this->api_key,
            UiTiDv1Environment::from($this->environment)
        );
    }
}
