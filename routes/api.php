<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AuditLogController;
use App\Http\Controllers\Api\GameController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\UserController;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');
Route::get('/games', [GameController::class, 'publicIndex']);
Route::get('/games/{slug}', [GameController::class, 'publicShow']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', fn (Request $request) => $request->user());

    Route::get('/orders', [OrderController::class, 'index'])->middleware('role:user');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->middleware('role:user');
    Route::post('/orders', [OrderController::class, 'store'])->middleware('role:user');
    Route::get('/tickets', [TicketController::class, 'index'])->middleware('role:user');
    Route::post('/tickets', [TicketController::class, 'store'])->middleware('role:user');

    Route::prefix('helper')->middleware('role:helper')->group(function () {
        Route::get('/tickets', [TicketController::class, 'helperIndex']);
        Route::patch('/tickets/{ticket}', [TicketController::class, 'helperUpdate']);
    });

    Route::prefix('admin')->middleware('role:admin')->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index']);
        Route::get('/orders', [OrderController::class, 'adminIndex']);
        Route::get('/orders/{order}', [OrderController::class, 'adminShow']);
        Route::patch('/orders/{order}', [OrderController::class, 'adminUpdate']);
        Route::apiResource('users', UserController::class);
        Route::get('/games', [GameController::class, 'index']);
        Route::post('/games', [GameController::class, 'store']);
        Route::get('/games/{game}', fn (\App\Models\Game $game) => response()->json(['data' => $game->load('packages')]));
        Route::match(['put', 'patch'], '/games/{game}', [GameController::class, 'update']);
        Route::delete('/games/{game}', [GameController::class, 'destroy']);
        Route::post('/games/{game}/packages', [GameController::class, 'storePackage']);
        Route::patch('/games/{game}/packages/{package}', [GameController::class, 'updatePackage']);
        Route::delete('/games/{game}/packages/{package}', [GameController::class, 'destroyPackage']);
    });
});
