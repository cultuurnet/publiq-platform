<?php

declare(strict_types=1);

namespace App\Domain\Auth\Controllers;

use App\Http\Controllers\Controller;
use Auth0\SDK\Auth0;
use Auth0\SDK\Token as Auth0Token;
use Illuminate\Http\JsonResponse;

final class Token extends Controller
{
    public function handle(string $idToken): JsonResponse
    {
        /** @var Auth0 $auth0 */
        $auth0 = app(Auth0::class);
        try {
            $token = new Auth0Token($auth0->configuration(), $idToken, Auth0Token::TYPE_ID_TOKEN);
            return  new JsonResponse($token->toArray());
        } catch (\Exception $exception) {
            return new JsonResponse(
                ['exception' => $exception->getMessage()],
                400
            );
        }
    }
}
