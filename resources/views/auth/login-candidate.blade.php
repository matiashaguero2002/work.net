@extends('layouts.guest')

@section('title', 'Iniciar sesión — Empleado')

@section('content')
<div id="view-login-candidate" class="wn-view active">
    <div class="wn-auth-layout">
        <div class="wn-auth-left">
            <h1>
                Bienvenido<br>
                <span class="accent">de vuelta.</span>
            </h1>
            <p>
                Iniciá sesión para buscar ofertas, guardar oportunidades
                y postularte a los puestos que te interesan.
            </p>
            <div class="wn-auth-features">
                <div class="wn-auth-feature">
                    <i class="bi bi-bookmark-fill"></i>
                    <span>Guardá ofertas para después</span>
                </div>
                <div class="wn-auth-feature">
                    <i class="bi bi-bell-fill"></i>
                    <span>Recibí notificaciones de nuevos puestos</span>
                </div>
                <div class="wn-auth-feature">
                    <i class="bi bi-graph-up-arrow"></i>
                    <span>Seguí el estado de tus postulaciones</span>
                </div>
            </div>
        </div>

        <div class="wn-auth-right">
            <div class="wn-card">
                <a href="{{ route('auth.role') }}" class="wn-back-btn" style="text-decoration: none;">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>

                <h2 class="wn-card-title">Iniciar sesión</h2>
                <p class="wn-card-subtitle">Accedé a tu cuenta de Empleado.</p>

                <form onsubmit="event.preventDefault(); window.location.href='{{ route('auth.dashboard.demo') }}'">
                    <div class="wn-form-group">
                        <label class="wn-label">Correo electrónico</label>
                        <div class="wn-input-icon">
                            <i class="bi bi-envelope"></i>
                            <input type="email" class="wn-input" placeholder="tu@email.com" autocomplete="email" required>
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
                    <a href="{{ route('auth.register.candidate') }}">
                        Crear cuenta
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
