<?php

declare(strict_types=1);

namespace App\Domain\Mail;

use Illuminate\Support\Collection;
use Symfony\Component\Mime\Address;

/**
 * @extends Collection<int, Address>
 */
final class Addresses extends Collection
{
}
