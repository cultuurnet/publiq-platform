<?php

declare(strict_types=1);

use App\Domain\Auth\Controllers\Login;
use App\Domain\Auth\Controllers\Logout;
use App\Domain\Integrations\Controllers\IntegrationController;
use App\Domain\Subscriptions\Controllers\SubscriptionController;
use Auth0\Laravel\Http\Controller\Stateful\Callback;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

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

Route::get('/logout', Logout::class);
Route::post('/admin/logout', Logout::class);

Route::get('/auth/callback', Callback::class);

Route::get('/subscriptions', [SubscriptionController::class, 'index']);

Route::group(['middleware' => 'auth'], static function () {
    Route::get('/integrations', [IntegrationController::class, 'index'])->name('integrations.index');
    Route::get('/integrations/create', [IntegrationController::class, 'create']);
    Route::post('/integrations', [IntegrationController::class, 'store']);
});
