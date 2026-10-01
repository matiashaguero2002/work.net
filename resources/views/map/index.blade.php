@extends('layouts.app')

@section('title', 'Work.net - Buscar ofertas')

@section('content')
<script>
    window.__OFFERS__ = @json($offers);
</script>

<div id="map"></div>

@include('partials.wn-filters')

@include('partials.wn-results-badge')

@include('partials.wn-offer-modal')
@endsection
