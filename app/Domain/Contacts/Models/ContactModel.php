<?php

declare(strict_types=1);

namespace App\Domain\Contacts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

final class ContactModel extends Model
{
    use SoftDeletes;

    protected $table = 'contact';

    protected $fillable = [
        'id',
        'type',
        'organization',
        'first_name',
        'last_name',
        'email',
    ];
}
