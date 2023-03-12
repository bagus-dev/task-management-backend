<?php

use App\Http\Controllers\API\ItemController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\API\TaskController;
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

Route::post('/login', [LoginController::class, 'index']);
Route::post('/logout', [LoginController::class, 'logout']);

Route::group(['middleware' => 'auth:sanctum'], function() {
    Route::patch('/tasks/done', [TaskController::class, 'updateDone']);
    Route::apiResource('tasks', TaskController::class);
    
    Route::patch('/items/done', [ItemController::class, 'updateDone']);
    Route::apiResource('items', ItemController::class);
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
