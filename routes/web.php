<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\McpConnectionController;
use App\Http\Controllers\TimezoneController;
use App\Http\Controllers\WinController;
use App\Http\Controllers\WinFeedController;
use Illuminate\Support\Facades\Route;

Route::view('/privacy', 'legal.privacy')->name('privacy');
Route::view('/terms', 'legal.terms')->name('terms');

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/wins', WinFeedController::class)->name('wins.feed');
    Route::post('/wins', [WinController::class, 'store'])->middleware('throttle:wins')->name('wins.store');
    Route::post('/timezone', TimezoneController::class)->name('timezone.update');
    Route::get('/connections', [McpConnectionController::class, 'index'])->name('connections.index');
    Route::delete('/connections/{client}', [McpConnectionController::class, 'destroy'])->whereUuid('client')->name('connections.destroy');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
