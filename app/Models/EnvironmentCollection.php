<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Integrations\Environment;
use Illuminate\Support\Collection;

/**
 * @extends Collection<int, Environment>
 */
final class EnvironmentCollection extends Collection
{
}
