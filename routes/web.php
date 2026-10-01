<?php

use App\Http\Controllers\Auth\AuthViewController;
use App\Http\Controllers\MapController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->name('auth.')->group(function () {
    Route::get('/rol', [AuthViewController::class, 'showRoleSelection'])->name('role');
    Route::get('/login/empleado', [AuthViewController::class, 'showLoginCandidate'])->name('login.candidate');
    Route::get('/login/empresa', [AuthViewController::class, 'showLoginEmployer'])->name('login.employer');
    Route::get('/registro/empleado', [AuthViewController::class, 'showRegisterCandidate'])->name('register.candidate');
    Route::get('/registro/empresa', [AuthViewController::class, 'showRegisterEmployer'])->name('register.employer');
    Route::get('/dashboard-demo', [AuthViewController::class, 'showDashboardDemo'])->name('dashboard.demo');
});

// Ruta raíz temporal → redirige a la selección de rol
Route::get('/', fn () => redirect()->route('auth.role'));

// Mapa de ofertas (público por ahora, sin middleware auth)
Route::get('/mapa', [MapController::class, 'index'])->name('map.index');
