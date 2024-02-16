<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('auth/register', [ProfileController::class, 'registration']);
Route::post('auth/login', [ProfileController::class, 'login']);
Route::get('auth/user', [ProfileController::class, 'userFetch'])->middleware('auth:sanctum');
Route::post('auth/logout', [ProfileController::class, 'logout'])->middleware('auth:sanctum');
Route::post('auth/reset',[ProfileController::class, 'resetPassword']);

Route::post('add-user', [ProfileController::class, 'addUser']);
Route::put('edit-user', [ProfileController::class, 'editUser']);
Route::delete('delete-user', [ProfileController::class, 'deleteUser']);
Route::get('get-user', [ProfileController::class, 'getUser']);
