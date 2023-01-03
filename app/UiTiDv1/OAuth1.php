<?php

declare(strict_types=1);

namespace App\UiTiDv1;

use GuzzleHttp\Psr7\Query;
use GuzzleHttp\Subscriber\Oauth\Oauth1 as BaseOauth1;
use Psr\Http\Message\RequestInterface;

final class OAuth1 extends BaseOauth1
{
    /**
     * Overridden to fix two issues in the base method when generating the base string for requests that have one or
     * more parameters with multiple values (like parameter=value1&parameter=value2).
     *
     * @see https://github.com/guzzle/oauth-subscriber/issues/69
     * @see https://github.com/guzzle/oauth-subscriber/pull/70
     */
    protected function createBaseString(RequestInterface $request, array $params): string
    {
        $url = (string) $request->getUri()->withQuery('');

        // Fix 1: Make sure that parameters with multiple values are sorted. They have to be sorted specifically by
        // their string value to generate a correct base string. For example [30,2,100] must be sorted as [100,2,30].
        $params = array_map(
            static function (mixed $value): mixed {
                if (is_array($value)) {
                    sort($value, SORT_STRING);
                }
                return $value;
            },
            $params
        );

        // Fix 2: Make sure to use GuzzleHttp\Psr7\Query::build() instead of http_build_query() to avoid parameters with
        // multiple values being encoded with a [] suffix.
        $query = Query::build($params);

        return strtoupper($request->getMethod())
            . '&' . rawurlencode($url)
            . '&' . rawurlencode($query);
    }
}
