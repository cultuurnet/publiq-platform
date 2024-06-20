<?php

declare(strict_types=1);

namespace App\Domain\Auth\Controllers;

use App\Domain\Auth\AuthenticationStrategy\AuthenticationStrategy;
use App\Domain\Auth\Models\UserModel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Facades\Auth;
use Psr\Log\LoggerInterface;

final readonly class CallbackController
{
    public function __construct(private AuthenticationStrategy $authenticationStrategy, private LoggerInterface $logger, private SessionManager $session)
    {
    }

    public function __invoke(Request $request): RedirectResponse
    {
        if ($this->authenticationStrategy->exchange($request)) {
            $user = $this->authenticationStrategy->getUser();
            if (!empty($this->authenticationStrategy->getIdToken())) {
                $this->session->put('id_token', $this->authenticationStrategy->getIdToken());
            }

            if ($user !== null) {
                $this->session->put('user', $user);
                $this->logger->debug(sprintf('User %s logged in', $user['name']));
                Auth::login(UserModel::fromSession($user));
            }
        }

        return redirect()->intended('/');
    }
}
