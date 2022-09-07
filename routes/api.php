<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CnaeFileController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route::post('/upload-cnae', [CnaeFileController::class], 'teste');
Route::post('/upload-cnae', [App\Http\Controllers\CnaeFileController::class, 'store']);
Route::get('/transactions', [App\Http\Controllers\CnaeFileController::class, 'index']);
Route::get('/balance', [App\Http\Controllers\CnaeFileController::class, 'getBalance']);
