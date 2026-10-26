<?php

use App\Http\Controllers\Auth\AuthViewController;
use App\Http\Controllers\Candidate\ApplicationController;
use App\Http\Controllers\Candidate\DashboardController;
use App\Http\Controllers\Candidate\InterviewController;
use App\Http\Controllers\Candidate\NotificationController;
use App\Http\Controllers\Candidate\ProfileController;
use App\Http\Controllers\Candidate\SavedOfferController;
use App\Http\Controllers\Employer\ApplicantController;
use App\Http\Controllers\Employer\CompanyController;
use App\Http\Controllers\Employer\DashboardController as EmployerDashboardController;
use App\Http\Controllers\Employer\InterviewController as EmployerInterviewController;
use App\Http\Controllers\Employer\NotificationController as EmployerNotificationController;
use App\Http\Controllers\Employer\OfferController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\OfferController as AdminOfferController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
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

// Módulo del empleador (público por ahora, sin middleware auth)
Route::prefix('empleador')->name('employer.')->group(function () {
    Route::get('/dashboard', [EmployerDashboardController::class, 'index'])->name('dashboard');

    Route::prefix('ofertas')->name('offers.')->group(function () {
        Route::get('/', [OfferController::class, 'index'])->name('index');
        Route::get('/crear', [OfferController::class, 'create'])->name('create');
        Route::post('/', [OfferController::class, 'store'])->name('store');
        Route::get('/{id}', [OfferController::class, 'show'])->name('show');
        Route::get('/{id}/editar', [OfferController::class, 'edit'])->name('edit');
        Route::put('/{id}', [OfferController::class, 'update'])->name('update');
        Route::delete('/{id}', [OfferController::class, 'destroy'])->name('destroy');
    });

    Route::get('/postulantes', [ApplicantController::class, 'index'])->name('applicants.index');
    Route::get('/postulantes/{id}', [ApplicantController::class, 'show'])->name('applicants.show');

    // Empresa
    Route::get('/empresa', [CompanyController::class, 'profile'])->name('company.profile');

    // Entrevistas (convocar)
    Route::get('/entrevistas/crear/{applicantId?}', [EmployerInterviewController::class, 'create'])->name('interviews.create');

    // Notificaciones del empleador
    Route::get('/notificaciones', [EmployerNotificationController::class, 'index'])->name('notifications.index');

    // Entrevistas del empleador (listado)
    Route::get('/entrevistas', [EmployerInterviewController::class, 'index'])->name('interviews.index');
});

// Módulo del administrador (público por ahora, sin middleware auth;
// datos mock hardcodeados en los controladores)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/usuarios', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/usuarios/{id}', [AdminUserController::class, 'show'])->name('users.show');
    Route::get('/ofertas', [AdminOfferController::class, 'index'])->name('offers.index');
    Route::get('/ofertas/{id}', [AdminOfferController::class, 'show'])->name('offers.show');
    Route::get('/reportes', [AdminReportController::class, 'index'])->name('reports.index');
});
