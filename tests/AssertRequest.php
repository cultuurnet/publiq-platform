<?php

declare(strict_types=1);

namespace Tests;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

trait AssertRequest
{
    private static function assertRequestIsTheSame(Request $expected, Request $actual): bool
    {
        self::assertEquals($expected->getHeaders(), $actual->getHeaders());
        self::assertEquals($expected->getMethod(), $actual->getMethod());
        self::assertEquals($expected->getBody()->getContents(), $actual->getBody()->getContents());

        return true;
    }

    private static function assertRequestResponseWithCallback(
        Request $firstRequest,
        Response $firstResponse,
        Request $secondRequest,
        Response $secondResponse,
    ): callable {
        return fn (Request $actualRequest) =>
            match ([$actualRequest->getHeaders(), $actualRequest->getMethod(), $actualRequest->getBody()->getContents()]) {
                [$firstRequest->getHeaders(), $firstRequest->getMethod(), $firstRequest->getBody()->getContents()] => $firstResponse,
                [$secondRequest->getHeaders(), $secondRequest->getMethod(), $secondRequest->getBody()->getContents()] => $secondResponse,
                default => throw new \LogicException('Invalid arguments received'),
            };
    }
}
