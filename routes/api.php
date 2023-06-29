<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\UserController;
use \App\Http\Controllers\EventController;
use \App\Http\Controllers\CommitteeController;
use \App\Http\Controllers\RoleController;
use \App\Http\Controllers\CalendarController;
use App\Enums\Permissions;

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

Route::middleware('api')->group(function () {
    Route::apiResources([
        'events' => EventController::class,
        'users' => UserController::class,
        'committees' => CommitteeController::class,
        'roles' => RoleController::class,
    ]);

    Route::get('permission-list', function () {
        $reflection = new \ReflectionClass(Permissions::class);
        $constants = array_values($reflection->getConstants());
        return response()->json(['data' => $constants]);
    });

    Route::get('profile', [UserController::class, 'profile']);
});

Route::post('login', [UserController::class, 'login']);
Route::post('calendar-excel', [CalendarController::class, 'excel']);
Route::post('calendar-login', [CalendarController::class, 'login']);
Route::post('calendar-token', [CalendarController::class, 'token']);
