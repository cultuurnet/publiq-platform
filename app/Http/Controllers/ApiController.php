<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;

final class ApiController extends Controller
{
    public function validateUser(Request $request): string
    {
        $idToken = $request->get('idToken');
        if ($idToken ===  $request->session()->get('id_token')) {
            return $idToken;
        }

        return 'false';
    }

}
