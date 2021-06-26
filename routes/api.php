<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TipsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PoolController;

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

Route::group(['prefix' => 'auth'], function () {
    Route::post('signin', [AuthController::class, 'signin']);
    Route::post('signup', [AuthController::class, 'signup']);
    
    Route::group(['middleware' => 'auth:api'], function () {
        Route::get('user', [AuthController::class, 'user']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

Route::resource('categories', CategoryController::class);
Route::resource('tips', TipsController::class);

Route::group(['middleware' => 'auth:api'], function () {
    Route::resource('pool', PoolController::class);
    Route::put('user/{id}', [AuthController::class, 'update']);
    Route::put('user/{id}/upload-image', [AuthController::class, 'uploadImage']);
});

Route::group(['prefix' => 'graphic'], function () {
    Route::post('day', [PoolController::class, 'graphicDay']);
    Route::post('week', [PoolController::class, 'graphicWeek']);
    Route::post('month', [PoolController::class, 'graphicMonth']);
});
