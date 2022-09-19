<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
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
}

//- Rotas que necessitam de autenticação
{
    Route::middleware('auth:sanctum')->group(function() {
        Route::controller(UserController::class)->group(function () {
            Route::get("/user", "show");
        });
    });
}
