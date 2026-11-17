@extends('layouts.app')

@section('title', 'Work.net - Publicar oferta')

@section('content')
    <div class="wn-bg"><div class="wn-bg-grid"></div></div>

    @include('partials.wn-navbar')

    <main class="wn-main wn-page-employer">
        @if (session('success'))
            <div class="wn-flash-success"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="wn-flash-error">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>@foreach ($errors->all() as $error)<span>{{ $error }}</span>@endforeach</div>
            </div>
        @endif

        <header class="wn-header">
            <div class="wn-header-left">
                <h1>Publicar nueva oferta</h1>
                <p>Completá la información del puesto. Los campos con <span style="color: var(--wn-danger);">*</span> son obligatorios.</p>
            </div>
            <div class="wn-header-actions">
                <a href="{{ route('employer.offers.index') }}" class="btn-wn-secondary">
                    <i class="bi bi-arrow-left"></i> Cancelar
                </a>
            </div>
        </header>

        <div class="wn-form-progress">
            <div class="wn-form-progress-step done"></div>
            <div class="wn-form-progress-step done"></div>
            <div class="wn-form-progress-step"></div>
            <div class="wn-form-progress-step"></div>
        </div>

        @include('employer.offers._form', ['offer' => [], 'ofertaSede' => $ofertaSede ?? null])
    </main>
@endsection
