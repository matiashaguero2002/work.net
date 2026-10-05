<?php

use App\Http\Controllers\Auth\AuthViewController;
use App\Http\Controllers\Candidate\ApplicationController;
use App\Http\Controllers\Candidate\DashboardController;
use App\Http\Controllers\Candidate\InterviewController;
use App\Http\Controllers\Candidate\NotificationController;
use App\Http\Controllers\Candidate\ProfileController;
use App\Http\Controllers\Candidate\SavedOfferController;
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

// Entrevistas del candidato (público por ahora, sin middleware auth)
Route::get('/entrevistas', [InterviewController::class, 'index'])->name('interviews.index');

// Dashboard del candidato (público por ahora, sin middleware auth)
Route::prefix('candidato')->name('candidate.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/perfil', [ProfileController::class, 'index'])->name('profile');
    Route::get('/postulaciones', [ApplicationController::class, 'index'])->name('applications.index');
    Route::get('/guardadas', [SavedOfferController::class, 'index'])->name('saved.index');
});

// Notificaciones (público por ahora, sin middleware auth)
Route::get('/notificaciones', [NotificationController::class, 'index'])->name('notifications.index');
