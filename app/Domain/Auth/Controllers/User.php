<?php

declare(strict_types=1);

namespace App\Domain\Auth\Controllers;

use App\Http\Controllers\Controller;
use Auth0\SDK\Auth0;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class User extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $idToken = $request->get('idToken');

        /** @var Auth0 $auth0 */
        $auth0 = app(Auth0::class);
        try {
        $token = $auth0->decode($idToken);

            return  new JsonResponse(
                $token->toArray()
            );
        } catch (\Exception $exception) {

            return new JsonResponse([
                'exception' => $exception->getMessage(),
            ],
            400
            );
        }
    }
}
