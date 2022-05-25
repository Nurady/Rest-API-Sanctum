<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\PostController;
use App\Http\Controllers\API\UserController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('profile', [UserController::class, 'getProfile']);
    Route::put('profile/update/{user}', [UserController::class, 'updateProfile']);

    Route::post('post/store', [PostController::class, 'store']);
    Route::put('post/update/{id}', [PostController::class, 'update']);
    Route::delete('post/delete/{id}', [PostController::class, 'delete']);

    Route::post('logout', [UserController::class, 'logout']);
});

Route::post('login', [UserController::class, 'login']);
Route::post('register', [UserController::class, 'register']);

Route::get('post', [PostController::class, 'index']);

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
