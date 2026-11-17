@extends('layouts.app')

@section('title', 'Work.net - Postulantes')

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
                <h1>Postulantes</h1>
                <p>Tenés <strong>{{ $stats['total'] }} postulaciones recibidas</strong> en tus ofertas.</p>
            </div>
        </header>

        <div class="wn-tabs" id="applicantsTabs">
            <button class="wn-tab active" type="button" data-filter="">Todas <span class="count">{{ $stats['total'] }}</span></button>
            <button class="wn-tab" type="button" data-filter="registered">Registradas <span class="count">{{ $stats['registered'] }}</span></button>
            <button class="wn-tab" type="button" data-filter="review">En revisión <span class="count">{{ $stats['in_review'] }}</span></button>
            <button class="wn-tab" type="button" data-filter="accepted">Aceptadas <span class="count">{{ $stats['accepted'] }}</span></button>
            <button class="wn-tab" type="button" data-filter="rejected">Rechazadas <span class="count">{{ $stats['rejected'] }}</span></button>
        </div>

        <div class="wn-toolbar">
            <div class="wn-search-input">
                <i class="bi bi-search"></i>
                <input type="text" id="applicantsSearch" placeholder="Buscar por nombre, oferta o habilidad...">
            </div>
            <select class="wn-sort-select" id="applicantsSort">
                <option value="recent">Más recientes</option>
                <option value="match">Mejor match</option>
                <option value="name">Nombre (A-Z)</option>
                <option value="offer">Oferta</option>
            </select>
        </div>

        <div class="wn-applicants-list" id="applicantsList">
            @foreach($applicants as $applicant)
                <article class="wn-applicant-card"@if($applicant['dimmed']) style="opacity: 0.7;"@endif
                    data-status="{{ $applicant['status'] }}"
                    data-name="{{ mb_strtolower($applicant['name']) }}"
                    data-offer="{{ mb_strtolower($applicant['offerTitle']) }}"
                    data-skills="{{ mb_strtolower(implode(' ', $applicant['skills'])) }}"
                    data-date="{{ $applicant['appliedTs'] }}"
                    data-match="{{ $applicant['match'] }}">
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
                            @if($applicant['hasCv'])
                                <a href="{{ route('employer.applicants.cv', $applicant['id']) }}" class="wn-applicant-action secondary"><i class="bi bi-file-earmark-pdf"></i> CV</a>
                            @endif
                        </div>
                    </div>
                </article>
            @endforeach

            @empty($applicants)
                <div class="wn-empty">
                    <div class="wn-empty-icon"><i class="bi bi-inbox"></i></div>
                    <h3>Sin postulaciones</h3>
                    <p>Cuando los candidatos se postulen a tus ofertas, los vas a ver acá.</p>
                </div>
            @endempty
        </div>

        <p id="applicantsResults" style="margin-top: 12px; font-size: 0.8rem; color: var(--wn-text-muted);"></p>
    </main>
@endsection

@push('scripts')
    <script>
        (function () {
            const list = document.getElementById('applicantsList');
            if (!list) return;
            const cards = Array.from(list.querySelectorAll('.wn-applicant-card'));
            const tabs = document.querySelectorAll('#applicantsTabs .wn-tab');
            const search = document.getElementById('applicantsSearch');
            const sort = document.getElementById('applicantsSort');
            const results = document.getElementById('applicantsResults');
            let activeFilter = '';

            function applyFilters() {
                const q = (search.value || '').toLowerCase().trim();
                let visible = 0;
                cards.forEach((card) => {
                    const match = (!activeFilter || card.dataset.status === activeFilter)
                        && (!q || card.dataset.name.includes(q) || card.dataset.offer.includes(q) || card.dataset.skills.includes(q));
                    card.style.display = match ? '' : 'none';
                    if (match) visible++;
                });
                if (results) results.textContent = visible + ' de ' + cards.length + ' postulaciones visibles';
            }

            tabs.forEach((tab) => {
                tab.addEventListener('click', () => {
                    tabs.forEach((t) => t.classList.remove('active'));
                    tab.classList.add('active');
                    activeFilter = tab.dataset.filter;
                    applyFilters();
                });
            });

            if (search) search.addEventListener('input', applyFilters);

            if (sort) {
                sort.addEventListener('change', () => {
                    const mode = sort.value;
                    const sorted = cards.slice().sort((a, b) => {
                        if (mode === 'recent') return new Date(b.dataset.date) - new Date(a.dataset.date);
                        if (mode === 'match') return b.dataset.match - a.dataset.match;
                        if (mode === 'name') return a.dataset.name.localeCompare(b.dataset.name);
                        if (mode === 'offer') return a.dataset.offer.localeCompare(b.dataset.offer);
                        return 0;
                    });
                    sorted.forEach((card) => list.appendChild(card));
                });
            }
        })();
    </script>
@endpush
