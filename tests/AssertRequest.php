<?php

declare(strict_types=1);

namespace Tests;

use GuzzleHttp\Psr7\Request;

trait AssertRequest
{
    private static function assertRequestIsTheSame(Request $expected, Request $actual): bool
    {
        self::assertEquals($expected->getHeaders(), $actual->getHeaders());
        self::assertEquals($expected->getMethod(), $actual->getMethod());
        self::assertEquals($expected->getBody()->getContents(), $actual->getBody()->getContents());

        return true;
    }
}
