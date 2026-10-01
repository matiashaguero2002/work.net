@extends('layouts.guest')

@section('title', 'Acceso')

@section('content')
<div class="wn-view active">
    <div class="wn-auth-layout">
        <div class="wn-auth-left">
            <h1>
                Encontrá tu<br>
                <span class="accent">próxima oportunidad</span><br>
                laboral.
            </h1>
            <p>
                Work.net centraliza las ofertas de empleo del área informática
                en Encarnación y te permite localizarlas en un mapa interactivo.
            </p>
            <div class="wn-auth-features">
                <div class="wn-auth-feature">
                    <i class="bi bi-geo-alt-fill"></i>
                    <span>Visualizá ofertas cercanas en el mapa</span>
                </div>
                <div class="wn-auth-feature">
                    <i class="bi bi-funnel-fill"></i>
                    <span>Filtrá por área, modalidad y salario</span>
                </div>
                <div class="wn-auth-feature">
                    <i class="bi bi-send-fill"></i>
                    <span>Postulate en un solo clic</span>
                </div>
            </div>
        </div>

        <div class="wn-auth-right">
            <div class="wn-card">
                <h2 class="wn-card-title">¿Cómo querés ingresar?</h2>
                <p class="wn-card-subtitle">Seleccioná el tipo de cuenta con la que vas a continuar.</p>

                <div class="wn-role-options">
                    <a href="{{ route('auth.login.candidate') }}" class="wn-role-btn" style="text-decoration: none;">
                        <div class="wn-role-icon">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <div class="wn-role-info">
                            <span class="wn-role-name">Soy Empleado</span>
                            <span class="wn-role-desc">Busco oportunidades laborales</span>
                        </div>
                    </a>

                    <a href="{{ route('auth.login.employer') }}" class="wn-role-btn" style="text-decoration: none;">
                        <div class="wn-role-icon">
                            <i class="bi bi-buildings-fill"></i>
                        </div>
                        <div class="wn-role-info">
                            <span class="wn-role-name">Soy Empresa</span>
                            <span class="wn-role-desc">Quiero publicar ofertas laborales</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
