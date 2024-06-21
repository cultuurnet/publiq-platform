<?php

declare(strict_types=1);

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\AuthenticationStrategy\AuthenticationStrategy;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

final class TokenController extends Controller
{
    public function __construct(private readonly AuthenticationStrategy $authenticationStrategy)
    {
    }

    public function handle(string $idToken): JsonResponse
    {
        try {
            return new JsonResponse($this->authenticationStrategy->createToken($idToken));
        } catch (\Exception $exception) {
            return new JsonResponse(
                ['exception' => $exception->getMessage()],
                400
            );
        }
    }
}
