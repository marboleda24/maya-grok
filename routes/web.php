<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PortfolioController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\StrategyController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return Inertia::render('Welcome', [
        'laravelVersion' => app()->version(), // Versión de Laravel
        'phpVersion' => PHP_VERSION           // Versión de PHP
    ]);
})->name('welcome');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard'); // Añadimos tu fragmento aquí

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Rutas para Portfolios (usando resource completo)
    Route::resource('portfolios', PortfolioController::class);

    // Rutas para Assets (usando resource anidado con shallow)
    Route::resource('portfolios.assets', AssetController::class)->shallow();

    // Ruta personalizada para operar activos
    Route::post('assets/{asset}/operate', [AssetController::class, 'operate'])->name('assets.operate');

    // Rutas para Strategies (CRUD completo excepto show)
    Route::resource('strategies', StrategyController::class)->except(['show']);
});

require __DIR__.'/auth.php';
