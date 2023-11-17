<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ApiController extends Controller
{
    public function validateUser(Request $request): JsonResponse
    {
        $idToken = $request->get('idToken');
        if ($idToken ===  $request->session()->get('id_token')) {
            return new JsonResponse([
                'user' => $idToken,
                'id' => 'id',
                'token' => 'token',
                'secret' => 'secret',
            ]);

        }

        return new JsonResponse([
            'user' => 'unknown'
        ]);
    }

}
