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
    public function index(Request $request):Response
    {
        $slackSuccess = $request->query->has('slackSuccess');
        $slackError = $request->query->has('slackError');

        return Inertia::render('Support/Index', [
            'email' => Auth::user()?->email,
            'slackSuccess' => $slackSuccess,
            'slackError' => $slackError,
        ]);
    }
    public function sendInvitation(Request $request):RedirectResponse
    {
        $botToken = 'xoxb-3000199719-6086825016259-VR1Qh7bZaGKybPt140PIvDwv';
        $channelID = 'C062K494YLA';
        $email = Auth::user()?->email;

        try {
            $response = Http::withToken($botToken)
                ->post('https://slack.com/api/conversations.inviteShared', [
                    'channel' => $channelID,
                    'emails' => $email,
                ]);

            if (!$response->ok()) {
                throw new \Exception('Response not ok');
            }

        } catch (\Throwable $th) {
            return Redirect::route(
                TranslatedRoute::getTranslatedRouteName($request, 'support.index'),
                ['slackError']
            );
        }
        return Redirect::route(
            TranslatedRoute::getTranslatedRouteName($request, 'support.index'),
            ['slackSuccess']
        );
    }
}
