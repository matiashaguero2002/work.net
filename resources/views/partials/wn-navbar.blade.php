@php
    // Determinar el rol del usuario
    if (auth()->check()) {
        // Hay sesión real → usar el rol del usuario
        $role = auth()->user()->rol;
    } elseif (request()->routeIs('employer.*')) {
        // No hay sesión pero estamos en ruta de empleador → modo demo
        $role = 'empleador';
    } elseif (request()->routeIs('admin.*')) {
        // No hay sesión pero estamos en ruta de admin → modo demo
        $role = 'admin';
    } elseif (request()->routeIs('candidate.*')) {
        // No hay sesión pero estamos en ruta de candidato → modo demo
        $role = 'candidato';
    } else {
        // Fallback general
        $role = 'candidato';
    }
@endphp

<nav class="wn-navbar" id="navbar">
    <div class="wn-navbar-brand">
        <div class="wn-navbar-brand-left" id="navbarBrandTrigger">
            @if($role === 'admin')
                <i class="bi bi-shield-lock-fill"></i>
            @else
                <i class="bi bi-briefcase-fill"></i>
            @endif
            <span>Work.net</span>
        </div>
        <button class="wn-navbar-toggle" id="navbarToggle" aria-label="Minimizar menú" type="button">
            <i class="bi bi-chevron-left"></i>
        </button>
    </div>

    <ul class="wn-navbar-menu">
        @if($role === 'candidato')
            {{-- ============================================
                 CARA 1: MENÚ DEL CANDIDATO
                 ============================================ --}}
            <li>
                <a href="{{ route('candidate.dashboard') }}" class="{{ request()->routeIs('candidate.dashboard') ? 'active' : '' }}" title="Inicio">
                    <i class="bi bi-house-door"></i> <span>Inicio</span>
                </a>
            </li>
            <li>
                <a href="{{ route('map.index') }}" class="{{ request()->routeIs('map.*') ? 'active' : '' }}" title="Buscar">
                    <i class="bi bi-map"></i> <span>Buscar</span>
                </a>
            </li>
            <li>
                <a href="{{ route('candidate.saved.index') }}" class="{{ request()->routeIs('candidate.saved.index') ? 'active' : '' }}" title="Guardadas">
                    <i class="bi bi-bookmark"></i> <span>Guardadas</span>
                </a>
            </li>
            <li>
                <a href="{{ route('candidate.applications.index') }}" class="{{ request()->routeIs('candidate.applications.*') ? 'active' : '' }}" title="Mis postulaciones">
                    <i class="bi bi-file-earmark-text"></i> <span>Mis postulaciones</span>
                </a>
            </li>
            <li>
                <a href="{{ route('interviews.index') }}" class="{{ request()->routeIs('interviews.*') ? 'active' : '' }}" title="Entrevistas">
                    <i class="bi bi-calendar-event"></i> <span>Entrevistas</span>
                </a>
            </li>
            <li>
                <a href="{{ route('candidate.profile') }}" class="{{ request()->routeIs('candidate.profile') ? 'active' : '' }}" title="Perfil">
                    <i class="bi bi-person"></i> <span>Perfil</span>
                </a>
            </li>

        @elseif($role === 'empleador')
            {{-- ============================================
                 CARA 2: MENÚ DEL EMPLEADOR
                 ============================================ --}}
            <li>
                <a href="{{ route('employer.dashboard') }}" class="{{ request()->routeIs('employer.dashboard') ? 'active' : '' }}" title="Inicio">
                    <i class="bi bi-house-door"></i> <span>Inicio</span>
                </a>
            </li>
            <li>
                <a href="{{ route('employer.offers.index') }}" class="{{ request()->routeIs('employer.offers.*') ? 'active' : '' }}" title="Ofertas">
                    <i class="bi bi-briefcase"></i> <span>Ofertas</span>
                </a>
            </li>
            <li>
                <a href="{{ route('employer.applicants.index') }}" class="{{ request()->routeIs('employer.applicants.index') ? 'active' : '' }}" title="Postulantes">
                    <i class="bi bi-people"></i> <span>Postulantes</span>
                </a>
            </li>
            <li>
                <a href="{{ route('employer.interviews.index') }}" class="{{ request()->routeIs('employer.interviews.*') ? 'active' : '' }}" title="Entrevistas">
                    <i class="bi bi-calendar-event"></i> <span>Entrevistas</span>
                </a>
            </li>
            <li>
                <a href="{{ route('employer.company.profile') }}" class="{{ request()->routeIs('employer.company.*') ? 'active' : '' }}" title="Mi empresa">
                    <i class="bi bi-building"></i> <span>Mi empresa</span>
                </a>
            </li>

        @elseif($role === 'admin')
            {{-- ============================================
                 CARA 3: MENÚ DEL ADMINISTRADOR
                 ============================================ --}}
            <li>
                <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" title="Panel">
                    <i class="bi bi-speedometer2"></i> <span>Panel</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}" title="Usuarios">
                    <i class="bi bi-people"></i> <span>Usuarios</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.offers.index') }}" class="{{ request()->routeIs('admin.offers.*') ? 'active' : '' }}" title="Ofertas">
                    <i class="bi bi-briefcase"></i> <span>Ofertas</span>
                </a>
            </li>
            <li>
                <a href="{{ route('admin.reports.index') }}" class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" title="Reportes">
                    <i class="bi bi-flag"></i> <span>Reportes</span> <span class="badge bg-danger" style="font-size:0.65rem;">2</span>
                </a>
            </li>
            <li>
                <a href="#" title="Configuración">
                    <i class="bi bi-gear"></i> <span>Configuración</span>
                </a>
            </li>
        @endif

        {{-- ============================================
             NOTIFICACIONES (común a candidato y empleador)
             ============================================ --}}
        @if($role !== 'admin')
        <li>
            @if($role === 'empleador')
                <a href="{{ route('employer.notifications.index') }}" class="{{ request()->routeIs('employer.notifications.*') ? 'active' : '' }}" title="Notificaciones">
            @else
                <a href="{{ route('notifications.index') }}" class="{{ request()->routeIs('notifications.*') ? 'active' : '' }}" title="Notificaciones">
            @endif
                <i class="bi bi-bell"></i> <span>Notificaciones</span>
                <?php $unreadNotifications = auth()->check() ? auth()->user()->notificaciones()->where('leida', false)->count() : 0; ?>
                @if ($unreadNotifications > 0)
                    <span class="badge bg-danger ms-auto" style="font-size:0.65rem;">{{ $unreadNotifications }}</span>
                @endif
            </a>
        </li>
        @endif
    </ul>

    @php
        // Datos del usuario para el footer
        if ($role === 'admin') {
            $userName = 'Admin General';
            $userEmail = 'admin@work.net';
        } elseif ($role === 'empleador') {
            $userName = 'Tech Solutions';
            $userEmail = 'contacto@techsolutions.com';
        } else {
            $userName = 'Juan Pérez';
            $userEmail = 'juan.perez@email.com';
        }

        // Si hay sesión real, mostrar los datos del usuario autenticado
        if (auth()->check()) {
            $authUser = auth()->user();
            $userEmail = $authUser->email;
            if ($role === 'empleador') {
                $empresa = $authUser->empleador;
                $userName = ($empresa && $empresa->nombre_empresa) ? $empresa->nombre_empresa : $authUser->nombre;
            } else {
                $userName = $authUser->nombre;
            }
        }

        // Iniciales a partir del nombre
        $parts = preg_split('/\s+/u', trim($userName));
        $userInitials = strtoupper(
            mb_substr($parts[0] ?? 'W', 0, 1) . mb_substr($parts[1] ?? 'N', 0, 1)
        );

        $userRole = match ($role) {
            'admin' => 'Administrador',
            'empleador' => 'Empleador',
            default => 'Candidato',
        };
    @endphp

    <div class="wn-navbar-footer">
        <div class="wn-user-info" id="userInfoTrigger">
            <div class="wn-avatar {{ $role === 'admin' ? 'wn-avatar-admin' : '' }}">{{ $userInitials }}</div>
            <div class="wn-user-text">
                <div class="wn-user-name">{{ $userName }}</div>
                <div class="wn-user-role">{{ $userRole }}</div>
            </div>
            <i class="bi bi-chevron-up wn-user-chevron"></i>
        </div>

        <div class="wn-user-dropdown" id="userDropdown">
            <div class="wn-user-dropdown-header">
                <p class="wn-user-dropdown-name">{{ $userName }}</p>
                <p class="wn-user-dropdown-email">{{ $userEmail }}</p>
            </div>

            @if($role === 'empleador')
                <a href="{{ route('employer.company.profile') }}" class="wn-user-dropdown-item"><i class="bi bi-building"></i> Mi empresa</a>
            @endif

            <a href="#" class="wn-user-dropdown-item"><i class="bi bi-gear"></i> Configuración</a>
            <a href="#" class="wn-user-dropdown-item"><i class="bi bi-question-circle"></i> Ayuda</a>
            <div class="wn-user-dropdown-divider"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="wn-user-dropdown-item danger"><i class="bi bi-box-arrow-right"></i> Cerrar sesión</button>
            </form>
        </div>
    </div>
</nav>
