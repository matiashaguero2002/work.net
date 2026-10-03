<nav class="wn-navbar" id="navbar">
    <div class="wn-navbar-brand">
        <div class="wn-navbar-brand-left" id="navbarBrandTrigger">
            <i class="bi bi-briefcase-fill"></i>
            <span>Work.net</span>
        </div>
        <button class="wn-navbar-toggle" id="navbarToggle" aria-label="Minimizar menú" type="button">
            <i class="bi bi-chevron-left"></i>
        </button>
    </div>

    <ul class="wn-navbar-menu">
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
            <a href="{{ route('candidate.applications.index') }}" class="{{ request()->routeIs('candidate.applications.index') ? 'active' : '' }}" title="Mis postulaciones">
                <i class="bi bi-file-earmark-text"></i> <span>Mis postulaciones</span>
            </a>
        </li>
        <li>
            <a href="{{ route('interviews.index') }}" class="{{ request()->routeIs('interviews.*') ? 'active' : '' }}" title="Entrevistas">
                <i class="bi bi-calendar-event"></i> <span>Entrevistas</span>
            </a>
        </li>
        <li>
            <a href="#" title="Perfil">
                <i class="bi bi-person"></i> <span>Perfil</span>
            </a>
        </li>
        <li>
            <a href="{{ route('notifications.index') }}" class="{{ request()->routeIs('notifications.*') ? 'active' : '' }}" title="Notificaciones">
                <i class="bi bi-bell"></i> <span>Notificaciones</span>
                <span class="badge bg-danger ms-auto" style="font-size:0.65rem;">3</span>
            </a>
        </li>
    </ul>

    <div class="wn-navbar-footer">
        <div class="wn-user-info">
            <div class="wn-avatar">JP</div>
            <div>
                <div class="wn-user-name">Juan Pérez</div>
                <div class="wn-user-role">Candidato</div>
            </div>
        </div>
    </div>
</nav>
