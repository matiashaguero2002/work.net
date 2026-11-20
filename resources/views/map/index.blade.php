@extends('layouts.app')

@section('title', 'Work.net - Buscar ofertas')

@section('content')
    <div id="map"
         data-offers='@json($offers)'
         data-focus='@json($mapFocus)'
         data-saved='@json($savedOfferIds)'
         data-postulated='@json($postulatedOfferIds)'
         data-save-url="{{ route('candidate.saved.store', [':idOferta']) }}"
         data-view-url="{{ auth()->check() && auth()->user()->candidato ? route('map.vista', [':idOferta']) : '' }}"
         data-post-url="{{ auth()->check() && auth()->user()->candidato ? route('candidate.applications.store', [':idOferta']) : '' }}"
         data-withdraw-url="{{ auth()->check() && auth()->user()->candidato ? route('candidate.applications.destroyByOffer', [':idOferta']) : '' }}"
     data-report-url="{{ route('reportes.store') }}"
     data-user-id="{{ auth()->check() && auth()->user()->id_usuario ?? 0 }}"></div>

    @include('partials.wn-navbar')
    @include('partials.wn-filters')
    @include('partials.wn-results-badge')
    @include('partials.wn-offer-modal')
@endsection
