@extends('layouts.guest')

@section('title', 'Iniciar sesión — Empresa')

@section('content')
<div id="view-login-employer" class="wn-view active">
    <div class="wn-auth-layout">
        <div class="wn-auth-left">
            <h1>
                Encontrá el<br>
                <span class="accent">talento ideal.</span>
            </h1>
            <p>
                Publicá ofertas, gestioná postulaciones y encontrá
                los mejores candidatos para tu empresa.
            </p>
            <div class="wn-auth-features">
                <div class="wn-auth-feature">
                    <i class="bi bi-plus-circle-fill"></i>
                    <span>Publicá ofertas ilimitadas</span>
                </div>
                <div class="wn-auth-feature">
                    <i class="bi bi-people-fill"></i>
                    <span>Gestioná postulaciones recibidas</span>
                </div>
                <div class="wn-auth-feature">
                    <i class="bi bi-calendar-check-fill"></i>
                    <span>Convocá entrevistas fácilmente</span>
                </div>
            </div>
        </div>

        <div class="wn-auth-right">
            <div class="wn-card">
                <a href="{{ route('auth.role') }}" class="wn-back-btn" style="text-decoration: none;">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>

                <h2 class="wn-card-title">Iniciar sesión</h2>
                <p class="wn-card-subtitle">Accedé a tu cuenta de Empresa.</p>

                <form onsubmit="event.preventDefault(); window.location.href='{{ route('auth.dashboard.demo') }}'">
                    <div class="wn-form-group">
                        <label class="wn-label">Correo corporativo</label>
                        <div class="wn-input-icon">
                            <i class="bi bi-envelope"></i>
                            <input type="email" class="wn-input" placeholder="contacto@empresa.com" autocomplete="email" required>
                        </div>
                    </div>

                    <div class="wn-form-group">
                        <label class="wn-label">Contraseña</label>
                        <div class="wn-input-icon">
                            <i class="bi bi-lock"></i>
                            <input type="password" class="wn-input" placeholder="••••••••" autocomplete="current-password" required>
                        </div>
                    </div>

                    <div style="text-align: right; margin-bottom: 18px;">
                        <a href="#" class="wn-link" style="font-size: 0.78rem;">¿Olvidaste tu contraseña?</a>
                    </div>

                    <button type="submit" class="btn-wn-primary">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Ingresar
                    </button>
                </form>

                <div class="wn-divider">o</div>

                <p class="wn-text-center">
                    ¿No tenés cuenta?
                    <a href="{{ route('auth.register.employer') }}">
                        Registrar empresa
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
