<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminAuditLogController;
use App\Http\Controllers\StoreController;

Route::get('/', fn () => view('welcome', ['games' => StoreController::games()]))->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/auth/google/redirect', [AuthController::class, 'redirectToGoogle'])->name('google.redirect');
    Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('google.callback');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [StoreController::class, 'dashboard'])->name('dashboard');
    Route::get('/admin/audit-logs', [AdminAuditLogController::class, 'index'])
        ->middleware('role:admin')
        ->name('admin.audit-logs');
    Route::get('/games/{slug}', [StoreController::class, 'game'])->name('game.show');
    Route::post('/games/{slug}/orders', [StoreController::class, 'order'])->middleware('role:user')->name('orders.store');
    Route::get('/support', [StoreController::class, 'support'])->middleware('role:user')->name('support.index');
    Route::post('/support', [StoreController::class, 'createTicket'])->middleware('role:user')->name('support.store');
    Route::post('/helper/tickets/{ticket}/reply', [StoreController::class, 'reply'])->middleware('role:helper')->name('support.reply');
});
