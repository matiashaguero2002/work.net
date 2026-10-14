@extends('layouts.app')

@section('title', 'Work.net - Postulantes')

@section('content')
    <div class="wn-bg"><div class="wn-bg-grid"></div></div>

    @include('partials.wn-navbar')

    <main class="wn-main wn-page-employer">
        <header class="wn-header">
            <div class="wn-header-left">
                <h1>Postulantes</h1>
                <p>Tenés <strong>{{ $stats['total'] }} postulaciones recibidas</strong> en tus ofertas activas.</p>
            </div>
        </header>

        <div class="wn-tabs">
            <button class="wn-tab active" type="button">Todas <span class="count">{{ $stats['total'] }}</span></button>
            <button class="wn-tab" type="button">Registradas <span class="count">{{ $stats['registered'] }}</span></button>
            <button class="wn-tab" type="button">En revisión <span class="count">{{ $stats['in_review'] }}</span></button>
            <button class="wn-tab" type="button">Aceptadas <span class="count">{{ $stats['accepted'] }}</span></button>
            <button class="wn-tab" type="button">Rechazadas <span class="count">{{ $stats['rejected'] }}</span></button>
        </div>

        <div class="wn-toolbar">
            <div class="wn-search-input">
                <i class="bi bi-search"></i>
                <input type="text" placeholder="Buscar por nombre, oferta o habilidad...">
            </div>
            <select class="wn-sort-select">
                <option>Más recientes</option>
                <option>Mejor match</option>
                <option>Nombre (A-Z)</option>
                <option>Oferta</option>
            </select>
        </div>

        <div class="wn-applicants-list">
            @foreach($applicants as $applicant)
                <article class="wn-applicant-card"@if($applicant['dimmed']) style="opacity: 0.7;"@endif>
                    <div class="wn-applicant-main">
                        <div class="wn-applicant-header">
                            <div class="wn-applicant-avatar-lg">{{ $applicant['initials'] }}</div>
                            <div class="wn-applicant-info">
                                <h3 class="wn-applicant-name-lg">{{ $applicant['name'] }}</h3>
                                <p class="wn-applicant-position">Postuló a: {{ $applicant['offerTitle'] }}</p>
                                <div class="wn-applicant-meta">
                                    @foreach($applicant['meta'] as $meta)
                                        <span><i class="bi {{ $meta['icon'] }}"></i> {{ $meta['text'] }}</span>
                                    @endforeach
                                </div>
                                <div class="wn-applicant-skills">
                                    @foreach($applicant['skills'] as $skill)
                                        <span class="wn-applicant-skill">{{ $skill }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="wn-applicant-side">
                        <span class="wn-applicant-status {{ $applicant['status'] }}"><i class="bi {{ $applicant['statusIcon'] }}"></i> {{ $applicant['statusLabel'] }}</span>
                        <div class="wn-applicant-match-lg"@if($applicant['lowMatch']) style="background-color: rgba(218, 54, 51, 0.08); border-color: rgba(218, 54, 51, 0.2);"@endif>
                            <div>
                                <div class="wn-applicant-match-value"@if($applicant['lowMatch']) style="color: var(--wn-danger);"@endif>{{ $applicant['match'] }}%</div>
                                <div class="wn-applicant-match-label">Match</div>
                            </div>
                        </div>
                        <div class="wn-applicant-actions">
                            @if(in_array('profile', $applicant['actions']))
                                <a href="{{ route('employer.applicants.show', $applicant['id']) }}" class="wn-applicant-action primary"><i class="bi bi-person-badge"></i> Ver perfil</a>
                            @endif
                            @if(in_array('cv', $applicant['actions']))
                                <a href="#" class="wn-applicant-action secondary"><i class="bi bi-file-earmark-pdf"></i> CV</a>
                            @endif
                            @if(in_array('interview', $applicant['actions']))
                                <a href="#" class="wn-applicant-action secondary"><i class="bi bi-calendar-plus"></i> Entrevistar</a>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </main>
@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.wn-tab').forEach(tab => {
            tab.addEventListener('click', () => {
                document.querySelectorAll('.wn-tab').forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
            });
        });
    </script>
@endpush
