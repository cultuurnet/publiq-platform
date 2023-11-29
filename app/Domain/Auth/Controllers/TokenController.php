<?php

declare(strict_types=1);

namespace App\Domain\Auth\Controllers;

use App\Http\Controllers\Controller;
use Auth0\SDK\Auth0;
use Auth0\SDK\Token;
use Illuminate\Http\JsonResponse;

final class TokenController extends Controller
{
    public function handle(string $idToken): JsonResponse
    {
        /** @var Auth0 $auth0 */
        $auth0 = app(Auth0::class);
        try {
            $token = new Token($auth0->configuration(), $idToken, Token::TYPE_ID_TOKEN);
            return new JsonResponse($token->toArray());
        } catch (\Exception $exception) {
            return new JsonResponse(
                ['exception' => $exception->getMessage()],
                400
            );
        }
    }
}
