<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Router\TranslatedRoute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

final class HomeController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        if (!Auth::check()) {
            return Inertia::render('Index');
        } else {
            return Redirect::route(
                TranslatedRoute::getTranslatedRouteName($request, 'integrations.index')
            );
        }
    }
}
