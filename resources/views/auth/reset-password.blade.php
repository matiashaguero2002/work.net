@extends('layouts.guest')

@section('title', 'Nueva contraseña')

@section('content')
<div id="view-reset" class="wn-view active">
    <div class="wn-auth-layout">
        <div class="wn-auth-left">
            <h1>
                Nueva<br>
                <span class="accent">contraseña</span>
            </h1>
            <p>
                Ingresá el código que recibiste por correo y elegí una
                contraseña nueva para tu cuenta.
            </p>
        </div>

        <div class="wn-auth-right">
            <div class="wn-card">
                <a href="{{ route('password.forgot') }}" class="wn-back-btn" style="text-decoration: none;">
                    <i class="bi bi-arrow-left"></i> Volver
                </a>

                <h2 class="wn-card-title">Restablecer contraseña</h2>
                <p class="wn-card-subtitle">Código enviado a <strong>{{ $email }}</strong>.</p>

                @if ($errors->any())
                    <div class="alert alert-danger py-2 px-3" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.reset.post') }}">
                    @csrf

                    <input type="hidden" name="email" value="{{ $email }}">
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="wn-form-group">
                        <label class="wn-label">Código de restablecimiento</label>
                        <div class="wn-input-icon">
                            <i class="bi bi-key"></i>
                            <input type="text" name="code" class="wn-input" placeholder="123456" maxlength="6" autocomplete="off" required>
                        </div>
                    </div>

                    <div class="wn-form-group">
                        <label class="wn-label">Nueva contraseña</label>
                        <div class="wn-input-icon">
                            <i class="bi bi-lock"></i>
                            <input type="password" name="password" class="wn-input" placeholder="••••••••" autocomplete="new-password" required>
                        </div>
                    </div>

                    <div class="wn-form-group">
                        <label class="wn-label">Confirmar nueva contraseña</label>
                        <div class="wn-input-icon">
                            <i class="bi bi-lock-fill"></i>
                            <input type="password" name="password_confirmation" class="wn-input" placeholder="••••••••" autocomplete="new-password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-wn-primary w-100">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Restablecer contraseña
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
