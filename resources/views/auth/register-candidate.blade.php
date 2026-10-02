@extends('layouts.guest')

@section('title', 'Crear cuenta — Empleado')

@section('content')
<div id="view-register-candidate" class="wn-view active">
    <div class="wn-auth-layout">
        <div class="wn-auth-left">
            <h1>
                Creá tu<br>
                <span class="accent">cuenta gratis.</span>
            </h1>
            <p>
                En pocos pasos vas a poder buscar ofertas, guardar
                oportunidades y postularte a los puestos que te interesan.
            </p>
            <div class="wn-auth-features">
                <div class="wn-auth-feature">
                    <i class="bi bi-shield-check"></i>
                    <span>Tu información está protegida</span>
                </div>
                <div class="wn-auth-feature">
                    <i class="bi bi-lightning-charge-fill"></i>
                    <span>Registro en menos de 2 minutos</span>
                </div>
                <div class="wn-auth-feature">
                    <i class="bi bi-check-circle-fill"></i>
                    <span>Sin costos ocultos</span>
                </div>
            </div>
        </div>

        <div class="wn-auth-right">
            <div class="wn-card">
                <a href="{{ route('auth.login.candidate') }}" class="wn-back-btn" style="text-decoration: none;">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>

                <h2 class="wn-card-title">Crear cuenta</h2>
                <p class="wn-card-subtitle">Completá tus datos para registrarte como Empleado.</p>

                <form onsubmit="event.preventDefault(); window.location.href='{{ route('auth.dashboard.demo') }}'">
                    <div class="wn-form-group">
                        <label class="wn-label">Nombre completo <span class="required">*</span></label>
                        <div class="wn-input-icon">
                            <i class="bi bi-person"></i>
                            <input type="text" class="wn-input" placeholder="Juan Pérez" autocomplete="name" required>
                        </div>
                    </div>

                    <div class="wn-form-group">
                        <label class="wn-label">Correo electrónico <span class="required">*</span></label>
                        <div class="wn-input-icon">
                            <i class="bi bi-envelope"></i>
                            <input type="email" class="wn-input" placeholder="tu@email.com" autocomplete="email" required>
                        </div>
                    </div>

                    <div class="wn-form-row">
                        <div class="wn-form-group">
                            <label class="wn-label">Contraseña <span class="required">*</span></label>
                            <div class="wn-input-icon">
                                <i class="bi bi-lock"></i>
                                <input type="password" class="wn-input" placeholder="••••••••" autocomplete="new-password" required>
                            </div>
                        </div>

                        <div class="wn-form-group">
                            <label class="wn-label">Confirmar <span class="required">*</span></label>
                            <div class="wn-input-icon">
                                <i class="bi bi-lock-fill"></i>
                                <input type="password" class="wn-input" placeholder="••••••••" autocomplete="new-password" required>
                            </div>
                        </div>
                    </div>

                    <div class="wn-form-group">
                        <label class="wn-label">Teléfono <span style="color: var(--wn-text-muted); font-weight: 400;">(opcional)</span></label>
                        <div class="wn-input-icon">
                            <i class="bi bi-telephone"></i>
                            <input type="tel" class="wn-input" placeholder="+595 9XX XXX XXX" autocomplete="tel">
                        </div>
                    </div>

                    <button type="submit" class="btn-wn-primary" style="margin-top: 6px;">
                        <i class="bi bi-person-plus"></i>
                        Crear cuenta
                    </button>

                    <p style="font-size: 0.7rem; color: var(--wn-text-muted); text-align: center; margin: 14px 0 0 0; line-height: 1.5;">
                        Al registrarte aceptás los <a href="#" class="wn-link">Términos de uso</a>
                        y la <a href="#" class="wn-link">Política de privacidad</a>.
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
