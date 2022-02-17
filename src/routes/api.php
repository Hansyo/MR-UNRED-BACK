<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\ReserveController;

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

Route::apiResource('reserve', ReserveController::class);
Route::apiResource('repitations', RepitationController::class);
Route::apiResource('rooms', RoomController::class);

Route::fallback(function(){
    return response()->json(['message' => 'Not Found'], 404);
});
