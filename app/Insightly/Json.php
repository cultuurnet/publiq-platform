<?php

declare(strict_types=1);

namespace App\Insightly;

final class Json
{
    /** @var int<512, 512> */
    private static int $depth = 512;

    public static function encode(mixed $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR, self::$depth);
    }

    public static function decodeAssociatively(string $json): mixed
    {
        return json_decode($json, true, self::$depth, JSON_THROW_ON_ERROR);
    }
}
