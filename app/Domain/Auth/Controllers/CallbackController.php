<?php

declare(strict_types=1);

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\Models\UserModel;
use Auth0\SDK\Auth0;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

final readonly class CallbackController
{
    public function __construct()
    {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        /** @var Auth0 $auth0 */
        $auth0 = app(Auth0::class);

        $code = $request->input('code');
        $state = $request->input('state');
        $pkce = session('code_verifier');

        Log::info('Starting manual Auth0 code exchange', [
            'code' => $code,
            'state' => $state,
            'pkce' => $pkce,
        ]);

        try {
            $response = $auth0->authentication()->codeExchange($code, null, $pkce);

            $body = $response->getBody()->__toString();
            $status = $response->getStatusCode();

            \Log::info('Auth0 codeExchange response', [
                'status' => $status,
                'body' => $body,
            ]);

            if ($status < 200 || $status >= 300) {
                \Log::error('Auth0 codeExchange failed', [
                    'status' => $status,
                    'body' => $body,
                ]);
                return redirect()->intended('/')->with('error', 'Authentication failed');
            }
        } catch (\Throwable $exception) {
            Log::error('Exception during Auth0 codeExchange', [
                'exception' => $exception,
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);
            return redirect()->intended('/')->with('error', 'Authentication failed');
        }

        $user = $auth0->getUser();
        if ($user === null) {
            return redirect()->intended('/');
        }

        Session::put('id_token', $auth0->getIdToken());

        Auth::login(UserModel::fromSession($user));

        return redirect()->intended('/');
    }
}
