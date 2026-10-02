@extends('layouts.guest')

@section('title', 'Bienvenido')

@section('content')
<div id="view-dashboard" class="wn-view active">
    <div class="wn-dashboard">
        <div class="wn-dashboard-icon">
            <i class="bi bi-check-lg"></i>
        </div>
        <h2>¡Bienvenido a Work.net!</h2>
        <p>
            Esta es una vista de prueba. En la versión final,
            acá se mostraría el dashboard correspondiente al rol del usuario.
        </p>
        <a href="{{ route('auth.role') }}" class="btn-wn-secondary" style="text-decoration: none;">
            <i class="bi bi-arrow-left"></i> Volver al inicio
        </a>
    </div>
</div>
@endsection
