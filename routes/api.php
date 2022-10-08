<?php

use App\Http\Controllers\PlugController;
use App\Http\Controllers\UserController;
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

//- Rotas sem necessidade de autenticação
{
    Route::controller(UserController::class)->group(function () {
        Route::post("/user", "store");
        Route::post("/login", "login");
    });

    Route::controller(PlugController::class)
        ->prefix("/plug")
        ->group(function () {
            Route::post("/", "store");

            Route::prefix("/{plug}/{token}")
                ->middleware("auth.plug")
                ->group(function () {
                    Route::get("/next-schedule", "getNextSchedule");
                    Route::post("/start-schedule", "startSchedule");
            });
    });
}

//- Rotas que necessitam de autenticação
{
    Route::middleware('auth:sanctum')->group(function() {
        Route::controller(UserController::class)->group(function () {
            Route::get("/user", "show");
            Route::post("/user/attach-plug/{plug}", "attachPlugToLoggedUser");
            Route::delete("/user/detach-plug/{plug}", "detachPlugFromLoggedUser");
        });

        Route::controller(PlugController::class)->group(function () {
            Route::post("/register-plug", "storeAndAttachToLoggedUser");
            Route::post("/{plug}/schedule", "newSchedule");
            Route::get("/{plug}/schedules", "listSchedules");
        });
    });
}
