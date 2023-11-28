<?php

declare(strict_types=1);

use App\Domain\Auth\Controllers\Access;
use App\Domain\Auth\Controllers\Token;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('ping', function () {
    return 'pong';
});

Route::get('token/{idToken}', [Token::class, 'handle']);
Route::get('token/{idToken}/integration/{id}', [Access::class, 'handle']);
