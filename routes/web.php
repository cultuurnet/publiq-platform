<?php

declare(strict_types=1);

use App\Domain\Subscriptions\Controllers\SubscriptionController;
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

Route::get('/subscriptions', [SubscriptionController::class, 'index']);
