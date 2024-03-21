<?php

declare(strict_types=1);

use App\Domain\Auth\Controllers\CallbackController;
use App\Domain\Auth\Controllers\LoginController;
use App\Domain\Auth\Controllers\LogoutController;
use App\Domain\Integrations\Controllers\IntegrationController;
use App\Domain\Subscriptions\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;
use App\Router\TranslatedRoute;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\SupportController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

TranslatedRoute::get(['/nl', '/en'], [HomeController::class, 'index']);

Route::get('/login', [LoginController::class, 'inertiaLogin'])->name('login');
Route::get('/admin/login', [LoginController::class, 'adminLogin']);

Route::get('/logout', [LogoutController::class, 'inertiaLogout']);
Route::post('/admin/logout', [LogoutController::class, 'adminLogout']);

Route::get('/auth/callback', CallbackController::class);

Route::group(['middleware' => 'auth'], static function () {
    TranslatedRoute::get(
        [
            '/en/support',
            '/nl/ondersteuning',
        ],
        [SupportController::class, 'index'],
        'support.index'
    );
});

Route::group(['middleware' => 'auth'], static function () {
    Route::post('/support/slack', [SupportController::class, 'sendInvitation']);
});

TranslatedRoute::get(
    [
        '/en/subscriptions',
        '/nl/abonnementen',
    ],
    [SubscriptionController::class, 'index']
);

Route::group(['middleware' => 'auth'], static function () {
    TranslatedRoute::get(
        [
            '/en/integrations',
            '/nl/integraties',
        ],
        [IntegrationController::class, 'index'],
        'integrations.index'
    );

    TranslatedRoute::get(
        [
            '/en/integrations/new',
            '/nl/integraties/nieuw',
        ],
        [IntegrationController::class, 'create']
    );
    TranslatedRoute::get(
        [
            '/en/integrations/{id}',
            '/nl/integraties/{id}',
        ],
        [IntegrationController::class, 'show'],
        'integrations.show'
    );

    Route::post('/integrations', [IntegrationController::class, 'store']);

    Route::group(['middleware' => 'can:access-integration,id'], static function () {
        Route::delete('/integrations/{id}', [IntegrationController::class, 'destroy']);
        Route::patch('/integrations/{id}', [IntegrationController::class, 'update']);

        Route::put('/integrations/{id}/urls', [IntegrationController::class, 'updateUrls']);
        Route::post('/integrations/{id}/urls', [IntegrationController::class, 'storeUrl']);
        Route::delete('/integrations/{id}/urls/{urlId}', [IntegrationController::class, 'destroyUrl']);

        Route::patch('/integrations/{id}/contacts', [IntegrationController::class, 'updateContacts']);
        Route::delete('/integrations/{id}/contacts/{contactId}', [IntegrationController::class, 'deleteContact']);

        Route::patch('/integrations/{id}/organization', [IntegrationController::class, 'updateOrganization']);

        Route::post('/integrations/{id}/activation', [IntegrationController::class, 'requestActivation']);

        // @deprecated
        Route::post('/integrations/{id}/coupon', [IntegrationController::class, 'activateWithCoupon']);
        // @deprecated
        Route::post('/integrations/{id}/organization', [IntegrationController::class, 'activateWithOrganization']);

        Route::post('/integrations/{id}/auth0-clients', [IntegrationController::class, 'distributeAuth0Clients']);

        Route::get('/integrations/{id}/widget', [IntegrationController::class, 'showWidget']);
    });
});
