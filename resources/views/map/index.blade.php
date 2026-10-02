@extends('layouts.app')

@section('title', 'Work.net - Buscar ofertas')

@section('content')
    <div id="map" data-offers='@json($offers)'></div>

    @include('partials.wn-navbar')
    @include('partials.wn-filters')
    @include('partials.wn-results-badge')
    @include('partials.wn-offer-modal')
@endsection
