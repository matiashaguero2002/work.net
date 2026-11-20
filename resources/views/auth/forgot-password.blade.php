@extends('layouts.guest')

@section('title', 'Restablecer contraseña')

@section('content')
<div id="view-forgot" class="wn-view active">
    <div class="wn-auth-layout">
        <div class="wn-auth-left">
            <h1>
                ¿Olvidaste tu<br>
                <span class="accent">contraseña?</span>
            </h1>
            <p>
                Ingresá tu correo electrónico y te enviaremos un código
                para crear una contraseña nueva.
            </p>
        </div>

        <div class="wn-auth-right">
            <div class="wn-card">
                <a href="{{ route('login') }}" class="wn-back-btn" style="text-decoration: none;">
                    <i class="bi bi-arrow-left"></i> Volver al inicio de sesión
                </a>

                <h2 class="wn-card-title">Restablecer contraseña</h2>
                <p class="wn-card-subtitle">Recuperá el acceso a tu cuenta de Work.net.</p>

                @if (session('success'))
                    <div class="alert alert-success py-2 px-3" role="alert">{{ session('success') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger py-2 px-3" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.forgot.post') }}">
                    @csrf

                    <div class="wn-form-group">
                        <label class="wn-label">Correo electrónico</label>
                        <div class="wn-input-icon">
                            <i class="bi bi-envelope"></i>
                            <input type="email" name="email" value="{{ old('email') }}" class="wn-input" placeholder="tu@email.com" autocomplete="email" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-wn-primary w-100">
                        <i class="bi bi-envelope"></i>
                        Enviar código
                    </button>
                </form>

                <div class="wn-divider mt-4">o</div>

                <p class="wn-text-center">
                    ¿Recordás tu contraseña?
                    <a href="{{ route('login') }}">
                        Iniciar sesión
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
