<?php

declare(strict_types=1);

namespace App\Domain\Contacts\Models;

use App\Models\UuidModel;
use Illuminate\Database\Eloquent\SoftDeletes;

final class ContactModel extends UuidModel
{
    use SoftDeletes;

    protected $table = 'contact';

    protected $fillable = [
        'id',
        'type',
        'first_name',
        'last_name',
        'email',
    ];
}
