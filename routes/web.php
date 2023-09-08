<?php

declare(strict_types=1);

use App\Domain\Auth\Controllers\Callback;
use App\Domain\Auth\Controllers\Login;
use App\Domain\Auth\Controllers\Logout;
use App\Domain\Integrations\Controllers\IntegrationController;
use App\Domain\Subscriptions\Controllers\SubscriptionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Router\TranslatedRoute;

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

Route::get('/', static fn () => Inertia::render('Index'));

Route::get('/login', Login::class)->name('login');
Route::get('/admin/login', static fn () => redirect('/login'));

Route::get('/logout', [Logout::class, 'inertia']);
Route::post('/admin/logout', [Logout::class, 'admin']);

Route::get('/auth/callback', Callback::class);

TranslatedRoute::get(['/en/support', '/nl/ondersteuning'], static fn () => Inertia::render('Support/Index'));
TranslatedRoute::get(['/en/subscriptions', '/nl/abonnementen'], [SubscriptionController::class, 'index']);

Route::group(['middleware' => 'auth'], static function () {
    TranslatedRoute::get(['/en/integrations', '/nl/integraties'], [IntegrationController::class, 'index'], 'integrations.index');
    TranslatedRoute::get(['/en/integrations/new', '/nl/integraties/nieuw'], [IntegrationController::class, 'create']);
    TranslatedRoute::get(['/en/integrations/{id}', '/nl/integraties/{id}'], [IntegrationController::class, 'detail'], 'integrations.detail');

    Route::delete('/integrations/{id}', [IntegrationController::class, 'delete']);

    Route::post('/integrations', [IntegrationController::class, 'store']);

    Route::patch('/integrations/{id}', [IntegrationController::class, 'update']);

    Route::patch('/integrations/{id}/contacts', [IntegrationController::class, 'updateContacts']);
    Route::delete('/integrations/{id}/contacts/{contactId}', [IntegrationController::class, 'deleteContact']);

    Route::patch('/integrations/{id}/billing', [IntegrationController::class, 'updateBilling']);
});
