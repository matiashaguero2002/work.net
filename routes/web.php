<?php

use App\Http\Controllers\Auth\AuthViewController;
use App\Http\Controllers\Candidate\ApplicationController;
use App\Http\Controllers\Candidate\CompanyController as CandidateCompanyController;
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

// Registro: crea usuario (+ candidato/empleador) y entra automáticamente
Route::post('/auth/registro/empleado', [AuthViewController::class, 'registerCandidate'])->name('auth.register.candidate.post');
Route::post('/auth/registro/empresa', [AuthViewController::class, 'registerEmployer'])->name('auth.register.employer.post');

// Alias con los nombres "naturales" de rol: si el usuario escribe
// /auth/login/candidato (el URI real es /empleado) no ve un 404
Route::redirect('/auth/login/candidato', '/auth/login/empleado', 301);
Route::redirect('/auth/registro/candidato', '/auth/registro/empleado', 301);

// Autenticación: login (RN-01, RN-02) y logout
Route::get('/login', [AuthViewController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthViewController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthViewController::class, 'logout'])->name('logout');

Route::get('/', function () {
    // Con sesión activa → panel según rol (RN-01)
    if (auth()->check()) {
        return match (auth()->user()->rol) {
            'candidato' => redirect()->route('candidate.dashboard'),
            'empleador' => redirect()->route('employer.dashboard'),
            'admin'     => redirect()->route('admin.dashboard'),
            default     => redirect()->route('login'),
        };
    }

    return redirect()->route('auth.role');
});

// Mapa de ofertas (público: la exploración no requiere cuenta)
Route::get('/mapa', [MapController::class, 'index'])->name('map.index');

// Entrevistas del candidato (solo cuentas de candidato)
Route::get('/entrevistas', [InterviewController::class, 'index'])
    ->middleware(['auth', 'role:candidato'])->name('interviews.index');

// Dashboard del candidato (solo cuentas de candidato)
Route::prefix('candidato')->name('candidate.')->middleware(['auth', 'role:candidato'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/perfil', [ProfileController::class, 'index'])->name('profile');

    // Perfil: formulario de datos personales
    Route::post('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    // Perfil: situación laboral
    Route::post('/perfil/situacion', [ProfileController::class, 'situacionUpdate'])->name('profile.situacion.update');
    // Perfil: experiencia laboral
    Route::post('/perfil/experiencia', [ProfileController::class, 'experienciaStore'])->name('profile.experiencia.store');
    Route::put('/perfil/experiencia/{idExperiencia}', [ProfileController::class, 'experienciaUpdate'])->name('profile.experiencia.update');
    Route::delete('/perfil/experiencia/{idExperiencia}', [ProfileController::class, 'experienciaDestroy'])->name('profile.experiencia.destroy');
    // Perfil: formación académica
    Route::post('/perfil/titulos', [ProfileController::class, 'tituloStore'])->name('profile.titulo.store');
    Route::put('/perfil/titulos/{idTitulo}', [ProfileController::class, 'tituloUpdate'])->name('profile.titulo.update');
    Route::delete('/perfil/titulos/{idTitulo}', [ProfileController::class, 'tituloDestroy'])->name('profile.titulo.destroy');
    // Perfil: habilidades e idiomas
    Route::post('/perfil/habilidades', [ProfileController::class, 'habilidadStore'])->name('profile.habilidad.store');
    Route::post('/perfil/habilidades/{idAptitud}/nivel', [ProfileController::class, 'habilidadUpdate'])->name('profile.habilidad.update');
    Route::delete('/perfil/habilidades/{idAptitud}', [ProfileController::class, 'habilidadDestroy'])->name('profile.habilidad.destroy');
    Route::post('/perfil/idiomas', [ProfileController::class, 'idiomaStore'])->name('profile.idioma.store');
    Route::delete('/perfil/idiomas/{idIdioma}', [ProfileController::class, 'idiomaDestroy'])->name('profile.idioma.destroy');
    // Perfil: CV
    Route::post('/perfil/cv', [ProfileController::class, 'cvStore'])->name('profile.cv.store');
    Route::delete('/perfil/cv', [ProfileController::class, 'cvDestroy'])->name('profile.cv.destroy');
    Route::get('/perfil/cv', [ProfileController::class, 'cvDownload'])->name('profile.cv.download');
    Route::get('/postulaciones', [ApplicationController::class, 'index'])->name('applications.index');
    Route::get('/guardadas', [SavedOfferController::class, 'index'])->name('saved.index');

    // Guardar / quitar ofertas (RF-08; el modal del mapa usa AJAX)
    Route::post('/ofertas/{idOferta}/guardar', [SavedOfferController::class, 'store'])->name('saved.store');
    Route::delete('/guardadas/{idOferta}', [SavedOfferController::class, 'destroy'])->name('saved.destroy');

    // Postularse / retirar postulación (el mapa y Guardadas usan AJAX)
    Route::post('/ofertas/{idOferta}/postular', [ApplicationController::class, 'store'])->name('applications.store');
    Route::delete('/ofertas/{idOferta}/postular', [ApplicationController::class, 'destroyByOffer'])->name('applications.destroyByOffer');
    Route::delete('/postulaciones/{idPostulacion}', [ApplicationController::class, 'destroy'])->name('applications.destroy');

    // Perfil de empresa visto por el candidato ("Ver empresa")
    Route::get('/empresa/{idEmpleador}', [CandidateCompanyController::class, 'show'])->name('company.show');
});

// Notificaciones del candidato (solo cuentas de candidato)
Route::middleware(['auth', 'role:candidato'])->group(function () {
    Route::get('/notificaciones', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notificaciones/leer-todas', [NotificationController::class, 'markAllRead'])->name('notifications.markAllRead');
    Route::post('/notificaciones/{idNotificacion}/leida', [NotificationController::class, 'markRead'])->name('notifications.markRead');
    Route::delete('/notificaciones/{idNotificacion}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});

// Acciones sobre entrevistas del candidato (cancelar/reagendar vía AJAX)
Route::prefix('candidato')->middleware(['auth', 'role:candidato'])->group(function () {
    Route::post('/entrevistas/{idEntrevista}/cancelar', [InterviewController::class, 'cancel'])->name('interviews.cancel');
    Route::post('/entrevistas/{idEntrevista}/negociar', [InterviewController::class, 'negotiate'])->name('interviews.negotiate');
});

// Módulo del empleador (solo cuentas de empresa)
Route::prefix('empleador')->name('employer.')->middleware(['auth', 'role:empleador'])->group(function () {
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

// Módulo del administrador (solo cuentas admin; datos mock hardcodeados en los controladores)
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::get('/usuarios', [AdminUserController::class, 'index'])->name('users.index');
    Route::get('/usuarios/{id}', [AdminUserController::class, 'show'])->name('users.show');
    Route::get('/ofertas', [AdminOfferController::class, 'index'])->name('offers.index');
    Route::get('/ofertas/{id}', [AdminOfferController::class, 'show'])->name('offers.show');
    Route::get('/reportes', [AdminReportController::class, 'index'])->name('reports.index');
});
