<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HostController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', [HostController::class, 'index'])->name('hosts.index');
    Route::post('/hosts', [HostController::class, 'store'])->name('hosts.store');
    Route::delete('/hosts/{host}', [HostController::class, 'destroy'])->name('hosts.destroy');
});
