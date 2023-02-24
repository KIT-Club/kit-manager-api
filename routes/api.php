<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\UserController;
use \App\Http\Controllers\EventController;
use \App\Http\Controllers\CommitteeController;
use \App\Http\Controllers\RoleController;
use \App\Http\Controllers\CalendarController;
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

Route::post('login', [UserController::class, 'login']);

Route::apiResources([
    'events' => EventController::class,
    'users' => UserController::class,
    'committees' => CommitteeController::class,
    'roles' => RoleController::class,
]);

Route::post('calendar-file', [CalendarController::class, 'file']);
Route::post('calendar-login', [CalendarController::class, 'login']);
