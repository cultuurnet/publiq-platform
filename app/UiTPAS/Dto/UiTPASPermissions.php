<?php

declare(strict_types=1);

namespace App\UiTPAS\Dto;

use App\Json;
use Illuminate\Support\Collection;

/**
 * @extends Collection<int, UiTPASPermission>
 */
final class UiTPASPermissions extends Collection
{
    public static function loadFromJson(string $json): self
    {
        return new self(array_map(
            static fn (array $item) => UiTPASPermission::fromArray($item),
            Json::decodeAssociatively($json)
        ));
    }
}
