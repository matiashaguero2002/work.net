@extends('layouts.app')

@section('title', 'Work.net - Usuarios')

@section('content')
    <div class="wn-bg"><div class="wn-bg-grid"></div></div>

    @include('partials.wn-navbar')

    <main class="wn-main wn-page-admin">

        <header class="wn-header">
            <div class="wn-header-left">
                <h1>Usuarios <span class="wn-admin-badge">Admin</span></h1>
                <p>Gestioná los usuarios registrados en la plataforma.</p>
            </div>
            <div class="wn-header-actions">
                <a href="{{ route('admin.dashboard') }}" class="btn-wn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al panel
                </a>
                <button type="button" class="btn-wn-primary">
                    <i class="bi bi-download"></i> Exportar CSV
                </button>
            </div>
        </header>

        {{-- STATS --}}
        <section class="wn-stats" aria-label="Resumen de usuarios">
            @foreach ($stats as $stat)
                <div class="wn-stat-card">
                    <div class="wn-stat-icon {{ $stat['modifier'] }}"><i class="bi bi-{{ $stat['icon'] }}"></i></div>
                    <div class="wn-stat-info">
                        <div class="wn-stat-value">{{ $stat['value'] }}</div>
                        <div class="wn-stat-label">{{ $stat['label'] }}</div>
                    </div>
                </div>
            @endforeach
        </section>

        {{-- FILTROS --}}
        <section class="wn-filters" aria-label="Filtros de usuarios">
            <div class="wn-filter-group wn-filter-search">
                <label class="wn-filter-label" for="filterSearch">Buscar</label>
                <i class="bi bi-search"></i>
                <input type="text" id="filterSearch" class="wn-filter-input" placeholder="Nombre, email o empresa...">
            </div>
            <div class="wn-filter-group">
                <label class="wn-filter-label" for="filterRole">Rol</label>
                <select id="filterRole" class="wn-filter-select">
                    <option value="">Todos</option>
                    <option value="candidato">Candidato</option>
                    <option value="empleador">Empleador</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>
            <div class="wn-filter-group">
                <label class="wn-filter-label" for="filterStatus">Estado</label>
                <select id="filterStatus" class="wn-filter-select">
                    <option value="">Todos</option>
                    <option value="activo">Activo</option>
                    <option value="pendiente">Pendiente</option>
                    <option value="suspendido">Suspendido</option>
                </select>
            </div>
            <div class="wn-filter-group">
                <label class="wn-filter-label" for="filterDate">Registro</label>
                <select id="filterDate" class="wn-filter-select">
                    <option value="">Cualquier fecha</option>
                    <option value="today">Hoy</option>
                    <option value="week">Esta semana</option>
                    <option value="month">Este mes</option>
                </select>
            </div>
            <button type="button" class="wn-filter-clear" id="filterClear">
                <i class="bi bi-x-lg"></i> Limpiar
            </button>
        </section>

        {{-- INFO + TABS --}}
        <div class="wn-results-info">
            <div class="wn-results-count">
                <strong id="resultsCount">{{ count($users) }}</strong> usuarios encontrados
            </div>
            <div class="wn-tabs" role="tablist" aria-label="Filtros rápidos">
                <button type="button" class="wn-tab active" data-tab="">Todos</button>
                <button type="button" class="wn-tab" data-tab="pendiente">Pendientes (3)</button>
                <button type="button" class="wn-tab" data-tab="suspendido">Suspendidos (47)</button>
            </div>
        </div>

        {{-- TABLA --}}
        <div class="wn-table-wrap">
            <table class="wn-table" id="usersTable">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Registro</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="usersTbody">
                    @foreach ($users as $user)
                        <tr data-role="{{ $user['role'] }}" data-status="{{ $user['status'] }}" data-name="{{ $user['name'] }}" data-email="{{ $user['email'] }}" data-date="{{ $user['registeredDate'] }}">
                            <td>
                                <a href="{{ route('admin.users.show', $user['id']) }}" class="wn-table-user" style="text-decoration:none;color:inherit;">
                                    <div class="wn-table-avatar">{{ $user['initials'] }}</div>
                                    <div>
                                        <p class="wn-table-name">{{ $user['name'] }}</p>
                                        <p class="wn-table-email">{{ $user['email'] }}</p>
                                    </div>
                                </a>
                            </td>
                            <td><span class="wn-role-badge {{ $user['role'] }}">{{ $user['roleLabel'] }}</span></td>
                            <td><span class="wn-status-badge {{ $user['status'] }}"><i class="bi bi-{{ $user['statusIcon'] }}"></i> {{ $user['statusLabel'] }}</span></td>
                            <td><span style="font-size: 0.8rem; color: var(--wn-text-muted);">{{ $user['registered'] }}</span></td>
                            <td>
                                <div class="wn-table-actions">
                                    <a href="{{ route('admin.users.show', $user['id']) }}" class="wn-icon-btn primary" title="Ver detalle"><i class="bi bi-eye"></i></a>
                                    @if (in_array('suspend', $user['actions']))
                                        <button type="button" class="wn-icon-btn danger" title="Suspender" data-action="suspend" data-name="{{ $user['name'] }}"><i class="bi bi-slash-circle"></i></button>
                                    @endif
                                    @if (in_array('approve', $user['actions']))
                                        <button type="button" class="wn-icon-btn" title="Aprobar" data-action="approve" data-name="{{ $user['name'] }}"><i class="bi bi-check-lg"></i></button>
                                    @endif
                                    @if (in_array('reject', $user['actions']))
                                        <button type="button" class="wn-icon-btn danger" title="Rechazar" data-action="reject" data-name="{{ $user['name'] }}"><i class="bi bi-x-lg"></i></button>
                                    @endif
                                    @if (in_array('reactivate', $user['actions']))
                                        <button type="button" class="wn-icon-btn" title="Reactivar" data-action="reactivate" data-name="{{ $user['name'] }}"><i class="bi bi-arrow-clockwise"></i></button>
                                    @endif
                                    @if (in_array('delete', $user['actions']))
                                        <button type="button" class="wn-icon-btn danger" title="Eliminar" data-action="delete" data-name="{{ $user['name'] }}"><i class="bi bi-trash"></i></button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- EMPTY STATE --}}
        <div class="wn-empty" id="emptyState" style="display: none;">
            <div class="wn-empty-icon"><i class="bi bi-person-x"></i></div>
            <h3>No encontramos usuarios</h3>
            <p>Probá ajustar los filtros de búsqueda o el estado seleccionado.</p>
        </div>

        {{-- PAGINACIÓN --}}
        <div class="wn-pagination">
            <div class="wn-pagination-info">
                Mostrando <strong>1-{{ count($users) }}</strong> de <strong>1.248</strong> usuarios
            </div>
            <div class="wn-pagination-controls">
                <button type="button" class="wn-page-btn" disabled><i class="bi bi-chevron-left"></i></button>
                <button type="button" class="wn-page-btn active">1</button>
                <button type="button" class="wn-page-btn">2</button>
                <button type="button" class="wn-page-btn">3</button>
                <button type="button" class="wn-page-btn">...</button>
                <button type="button" class="wn-page-btn">156</button>
                <button type="button" class="wn-page-btn"><i class="bi bi-chevron-right"></i></button>
            </div>
        </div>

    </main>

    {{-- MODAL ACCIÓN --}}
    <div class="wn-modal-backdrop wn-page-admin" id="actionModal" role="dialog" aria-modal="true" aria-labelledby="actionModalTitle">
        <div class="wn-modal">
            <div class="wn-modal-icon" id="actionModalIcon">
                <i class="bi bi-exclamation-triangle-fill"></i>
            </div>
            <div>
                <h2 id="actionModalTitle">¿Confirmar acción?</h2>
                <p id="actionModalText">Esta acción se aplicará al usuario seleccionado.</p>
            </div>
            <div class="wn-modal-actions">
                <button type="button" class="btn-wn-secondary" id="actionModalNo">
                    <i class="bi bi-x-lg"></i> Cancelar
                </button>
                <button type="button" class="btn-wn-primary" id="actionModalYes">
                    <i class="bi bi-check-lg"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
@endsection
