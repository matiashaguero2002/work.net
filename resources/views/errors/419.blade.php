@extends('layouts.guest')

@section('title', 'Sesión vencida')

@section('content')
<div class="wn-view active">
    <div class="wn-auth-layout">
        <div class="wn-auth-right" style="margin: 0 auto;">
            <div class="wn-card" style="text-align: center;">
                <h2 class="wn-card-title">Tu sesión de ingreso venció</h2>
                <p class="wn-card-subtitle">
                    La página quedó abierta demasiado tiempo o se restauró desde el
                    historial del navegador. Volvé a intentar el ingreso con tus datos.
                </p>
                <p style="margin: 20px 0;">
                    <a href="{{ route('auth.role') }}" class="btn-wn-primary" style="text-decoration: none;">
                        Ir a la selección de acceso
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
