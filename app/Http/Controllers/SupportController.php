<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Router\TranslatedRoute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

final class SupportController extends Controller
{
    public function index(Request $request): Response
    {
        $slackStatus = $request->query->get('slackStatus');

        return Inertia::render('Support/Index', [
            'email' => Auth::user()?->email,
            'slackStatus' => $slackStatus,
        ]);
    }

    public function sendInvitation(Request $request): RedirectResponse
    {
        $botToken = config('slack.botToken');
        $channelID = config('slack.channels.technical_support');
        $email = $request->validate(['email' => ['required', 'email']])['email'];

        try {
            $response = Http::withToken($botToken)
                ->post('https://slack.com/api/conversations.inviteShared', [
                    'channel' => $channelID,
                    'emails' => [$email],
                ]);

            $body = json_decode(
                json: $response->body(),
                associative: true,
                flags: JSON_THROW_ON_ERROR
            );

            if (!$response->ok() || $body['ok'] === false) {
                throw new \Exception('Response not ok');
            }

        } catch (\Throwable $th) {
            return Redirect::route(
                TranslatedRoute::getTranslatedRouteName($request, 'support.index'),
                ['slackStatus' => 'error']
            );
        }
        return Redirect::route(
            TranslatedRoute::getTranslatedRouteName($request, 'support.index'),
            ['slackStatus' => 'success']
        );
    }
}
