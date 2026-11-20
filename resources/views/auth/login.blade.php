@extends('layouts.guest')

@section('title', 'Iniciar sesión')

@section('content')
<div id="view-login" class="wn-view active">
    <div class="wn-auth-layout">
        <div class="wn-auth-left">
            <h1>
                Bienvenido<br>
                <span class="accent">de vuelta.</span>
            </h1>
            <p>
                Iniciá sesión con tu cuenta de Work.net y seguí
                conectado al talento y a las oportunidades.
            </p>
            <div class="wn-auth-features">
                <div class="wn-auth-feature">
                    <i class="bi bi-map-fill"></i>
                    <span>Explorá ofertas en el mapa</span>
                </div>
                <div class="wn-auth-feature">
                    <i class="bi bi-bookmark-fill"></i>
                    <span>Guardá tus favoritos</span>
                </div>
                <div class="wn-auth-feature">
                    <i class="bi bi-bell-fill"></i>
                    <span>Recibí notificaciones al instante</span>
                </div>
            </div>
        </div>

        <div class="wn-auth-right">
            <div class="wn-card">
                <a href="{{ route('auth.role') }}" class="wn-back-btn" style="text-decoration: none;">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>

                <h2 class="wn-card-title">Iniciar sesión</h2>
                <p class="wn-card-subtitle">Accedé a tu cuenta de Work.net.</p>

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf

                    @error('email')
                        <div class="alert alert-danger py-2 px-3" role="alert">{{ $message }}</div>
                    @enderror

                    @error('password')
                        <div class="alert alert-danger py-2 px-3" role="alert">{{ $message }}</div>
                    @enderror

                    <div class="wn-form-group">
                        <label class="wn-label">Correo electrónico</label>
                        <div class="wn-input-icon">
                            <i class="bi bi-envelope"></i>
                            <input type="email" name="email" value="{{ old('email') }}" class="wn-input" placeholder="tu@email.com" autocomplete="email" required>
                        </div>
                    </div>

                    <div class="wn-form-group">
                        <label class="wn-label">Contraseña</label>
                        <div class="wn-input-icon">
                            <i class="bi bi-lock"></i>
                            <input type="password" name="password" class="wn-input" placeholder="••••••••" autocomplete="current-password" required>
                        </div>
                        <label class="wn-label mt-2 mb-0" style="font-size: 0.75rem; display: flex; align-items: center; gap: 6px; cursor: pointer; font-weight: 500;">
                            <input type="checkbox" name="remember" class="form-check-input m-0"> Recordarme
                        </label>
                    </div>

                    <div style="text-align: right; margin-bottom: 18px;">
                        <a href="{{ route('password.forgot') }}" class="wn-link" style="font-size: 0.78rem;">¿Te olvidaste de la contraseña?</a>
                    </div>

                    <button type="submit" class="btn-wn-primary">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Ingresar
                    </button>
                </form>

                <div class="wn-divider">o</div>

                <p class="wn-text-center">
                    ¿No tenés cuenta?
                    <a href="{{ route('auth.role') }}">
                        Crear cuenta
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
