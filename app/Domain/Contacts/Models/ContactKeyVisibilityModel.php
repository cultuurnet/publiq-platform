<?php

declare(strict_types=1);

namespace App\Domain\Contacts\Models;

use App\Domain\Integrations\KeyVisibility;
use App\Models\UuidModel;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $email
 * @property KeyVisibility $key_visibility
 */
final class ContactKeyVisibilityModel extends UuidModel
{
    use SoftDeletes;

    protected $table = 'contacts_key_visibility';

    protected $fillable = [
        'id',
        'email',
        'key_visibility',
    ];

    protected $casts = [
        'key_visibility' => KeyVisibility::class,
    ];
}
