@extends('layouts.guest')

@section('title', 'Registrar empresa')

@section('content')
<div class="wn-view active">
    <div class="wn-auth-layout">
        <div class="wn-auth-left">
            <h1>
                Sumá tu<br>
                <span class="accent">empresa.</span>
            </h1>
            <p>
                Publicá ofertas, recibí postulaciones y gestioná
                todo el proceso de selección desde un solo lugar.
            </p>
            <div class="wn-auth-features">
                <div class="wn-auth-feature">
                    <i class="bi bi-building-check"></i>
                    <span>Perfil público de tu empresa</span>
                </div>
                <div class="wn-auth-feature">
                    <i class="bi bi-bar-chart-fill"></i>
                    <span>Estadísticas de tus ofertas</span>
                </div>
                <div class="wn-auth-feature">
                    <i class="bi bi-people-fill"></i>
                    <span>Base de candidatos disponibles</span>
                </div>
            </div>
        </div>

        <div class="wn-auth-right">
            <div class="wn-card">
                <a href="{{ route('auth.login.employer') }}" class="wn-back-btn" style="text-decoration: none;">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>

                <h2 class="wn-card-title">Registrar empresa</h2>
                <p class="wn-card-subtitle">Completá los datos de tu organización.</p>

                <form onsubmit="event.preventDefault(); window.location.href='{{ route('auth.dashboard.demo') }}'">
                    <div class="wn-form-group">
                        <label class="wn-label">Nombre de la empresa <span class="required">*</span></label>
                        <div class="wn-input-icon">
                            <i class="bi bi-building"></i>
                            <input type="text" class="wn-input" placeholder="Tech Solutions S.A." required>
                        </div>
                    </div>

                    <div class="wn-form-group">
                        <label class="wn-label">RUC <span class="required">*</span></label>
                        <div class="wn-input-icon">
                            <i class="bi bi-card-text"></i>
                            <input type="text" class="wn-input" placeholder="80012345-6" required>
                        </div>
                    </div>

                    <div class="wn-form-group">
                        <label class="wn-label">Correo corporativo <span class="required">*</span></label>
                        <div class="wn-input-icon">
                            <i class="bi bi-envelope"></i>
                            <input type="email" class="wn-input" placeholder="contacto@empresa.com" required>
                        </div>
                    </div>

                    <div class="wn-form-row">
                        <div class="wn-form-group">
                            <label class="wn-label">Contraseña <span class="required">*</span></label>
                            <div class="wn-input-icon">
                                <i class="bi bi-lock"></i>
                                <input type="password" class="wn-input" placeholder="••••••••" required>
                            </div>
                        </div>

                        <div class="wn-form-group">
                            <label class="wn-label">Confirmar <span class="required">*</span></label>
                            <div class="wn-input-icon">
                                <i class="bi bi-lock-fill"></i>
                                <input type="password" class="wn-input" placeholder="••••••••" required>
                            </div>
                        </div>
                    </div>

                    <div class="wn-form-group">
                        <label class="wn-label">Ubicación <span class="required">*</span></label>
                        <div class="wn-input-icon">
                            <i class="bi bi-geo-alt"></i>
                            <input type="text" class="wn-input" placeholder="Encarnación, Paraguay" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-wn-primary" style="margin-top: 6px;">
                        <i class="bi bi-building-add"></i>
                        Registrar empresa
                    </button>

                    <p style="font-size: 0.7rem; color: var(--wn-text-muted); text-align: center; margin: 14px 0 0 0; line-height: 1.5;">
                        Al registrar tu empresa aceptás los <a href="#" class="wn-link">Términos de uso</a>
                        y la <a href="#" class="wn-link">Política de privacidad</a>.
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
