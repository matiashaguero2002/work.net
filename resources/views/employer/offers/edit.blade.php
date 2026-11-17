@extends('layouts.app')

@section('title', 'Work.net - Editar oferta')

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
                <h1>Editar oferta</h1>
                <p>Modificá la información del puesto según sea necesario.</p>
            </div>
            <div class="wn-header-actions">
                <a href="{{ route('employer.offers.show', $offer['id']) }}" class="btn-wn-secondary">
                    <i class="bi bi-arrow-left"></i> Cancelar
                </a>
            </div>
        </header>

        @include('employer.offers._form', ['offer' => $offer, 'ofertaSede' => $ofertaSede ?? null])
    </main>
@endsection
