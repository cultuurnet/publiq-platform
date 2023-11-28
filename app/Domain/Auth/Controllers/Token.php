<?php

declare(strict_types=1);

namespace App\Domain\Auth\Controllers;

use App\Http\Controllers\Controller;
use Auth0\SDK\Auth0;
use Illuminate\Http\JsonResponse;

final class Token extends Controller
{
    public function handle(string $idToken): JsonResponse
    {
        /** @var Auth0 $auth0 */
        $auth0 = app(Auth0::class);
        try {
            $token = $auth0->decode($idToken);
            return  new JsonResponse($token->toArray());
        } catch (\Exception $exception) {
            return new JsonResponse(
                ['exception' => $exception->getMessage()],
                400
            );
        }
    }
}
