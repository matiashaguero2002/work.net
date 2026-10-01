{{-- Navbar lateral reutilizable. Recibe $role opcional: 'candidato' (defecto), 'empleador', 'admin'. --}}
@php
    $role = $role ?? 'candidato';

    $menus = [
        'candidato' => [
            ['label' => 'Inicio', 'icon' => 'bi-house-door', 'url' => '#', 'active' => false],
            ['label' => 'Buscar', 'icon' => 'bi-map', 'url' => route('map.index'), 'active' => request()->routeIs('map.index')],
            ['label' => 'Guardadas', 'icon' => 'bi-bookmark', 'url' => '#', 'active' => false],
            ['label' => 'Mis postulaciones', 'icon' => 'bi-file-earmark-text', 'url' => '#', 'active' => false],
            ['label' => 'Perfil', 'icon' => 'bi-person', 'url' => '#', 'active' => false],
            ['label' => 'Notificaciones', 'icon' => 'bi-bell', 'url' => '#', 'active' => false, 'badge' => 3],
        ],
        'empleador' => [
            ['label' => 'Inicio', 'icon' => 'bi-house-door', 'url' => '#', 'active' => false],
            ['label' => 'Mis ofertas', 'icon' => 'bi-briefcase', 'url' => '#', 'active' => false],
            ['label' => 'Publicar oferta', 'icon' => 'bi-plus-circle', 'url' => '#', 'active' => false],
            ['label' => 'Postulaciones recibidas', 'icon' => 'bi-people', 'url' => '#', 'active' => false],
            ['label' => 'Perfil', 'icon' => 'bi-person', 'url' => '#', 'active' => false],
            ['label' => 'Notificaciones', 'icon' => 'bi-bell', 'url' => '#', 'active' => false, 'badge' => 3],
        ],
        'admin' => [
            ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'url' => '#', 'active' => false],
            ['label' => 'Usuarios', 'icon' => 'bi-people', 'url' => '#', 'active' => false],
            ['label' => 'Ofertas', 'icon' => 'bi-briefcase', 'url' => '#', 'active' => false],
            ['label' => 'Perfil', 'icon' => 'bi-person', 'url' => '#', 'active' => false],
        ],
    ];

    $menu = $menus[$role] ?? $menus['candidato'];

    // Mock temporal (sin auth real). Más adelante: auth()->user().
    $mockUsers = [
        'candidato' => ['name' => 'Juan Pérez', 'role' => 'Candidato', 'initials' => 'JP'],
        'empleador' => ['name' => 'Tech Solutions S.A.', 'role' => 'Empresa', 'initials' => 'TS'],
        'admin' => ['name' => 'Admin', 'role' => 'Administrador', 'initials' => 'AD'],
    ];
    $mockUser = $mockUsers[$role] ?? $mockUsers['candidato'];
@endphp

<nav class="wn-navbar">
    <div class="wn-navbar-brand">
        <i class="bi bi-briefcase-fill"></i>
        <span>Work.net</span>
    </div>

    <ul class="wn-navbar-menu">
        @foreach ($menu as $item)
            <li>
                <a href="{{ $item['url'] }}" @class(['active' => $item['active']])>
                    <i class="bi {{ $item['icon'] }}"></i> <span>{{ $item['label'] }}</span>
                    @isset($item['badge'])
                        <span class="badge bg-danger ms-auto" style="font-size:0.65rem;">{{ $item['badge'] }}</span>
                    @endisset
                </a>
            </li>
        @endforeach
    </ul>

    <div class="wn-navbar-footer">
        <div class="wn-user-info">
            <div class="wn-avatar">{{ $mockUser['initials'] }}</div>
            <div>
                <div class="wn-user-name">{{ $mockUser['name'] }}</div>
                <div class="wn-user-role">{{ $mockUser['role'] }}</div>
            </div>
        </div>
    </div>
</nav>
