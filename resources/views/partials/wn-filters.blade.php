<aside class="wn-filters" id="filtersPanel">
    <div class="wn-filters-header" onclick="toggleFilters()">
        <div class="wn-filters-title">
            <i class="bi bi-funnel-fill"></i>
            <span>Filtros</span>
        </div>
        <button class="wn-filters-toggle" type="button" aria-label="Minimizar filtros">
            <i class="bi bi-chevron-up"></i>
        </button>
    </div>

    <div class="wn-filters-body">
        <div class="wn-filter-group">
            <label class="wn-filter-label">Buscar</label>
            <div class="wn-filter-search">
                <i class="bi bi-search"></i>
                <input type="text" class="wn-filter-input" placeholder="Cargo, empresa...">
            </div>
        </div>

        <div class="wn-filter-group">
            <label class="wn-filter-label">Área laboral</label>
            <select class="wn-filter-select">
                <option value="">Todas las áreas</option>
                <option value="informatica" selected>Informática / TI</option>
                <option value="diseno">Diseño</option>
                <option value="marketing">Marketing</option>
                <option value="admin">Administración</option>
            </select>
        </div>

        <div class="wn-filter-group">
            <label class="wn-filter-label">Modalidad</label>
            <div class="wn-filter-chips">
                <label class="wn-filter-chip">
                    <input type="checkbox" value="presencial">
                    <i class="bi bi-building"></i> Presencial
                </label>
                <label class="wn-filter-chip">
                    <input type="checkbox" value="remoto" checked>
                    <i class="bi bi-house"></i> Remoto
                </label>
                <label class="wn-filter-chip">
                    <input type="checkbox" value="hibrido">
                    <i class="bi bi-arrow-left-right"></i> Híbrido
                </label>
            </div>
        </div>

        <div class="wn-filter-group">
            <label class="wn-filter-label">Tipo de contrato</label>
            <select class="wn-filter-select">
                <option value="">Todos</option>
                <option value="tiempo-completo">Tiempo completo</option>
                <option value="medio-tiempo">Medio tiempo</option>
                <option value="pasantia">Pasantía</option>
                <option value="contrato">Por contrato</option>
                <option value="freelance">Freelance</option>
            </select>
        </div>

        <div class="wn-filter-group">
            <label class="wn-filter-label">Remuneración mínima (Gs.)</label>
            <input type="text" class="wn-filter-input" placeholder="Ej: 5.000.000">
        </div>

        <div class="wn-filter-group">
            <label class="wn-filter-label">Distancia máxima</label>
            <input type="range" class="wn-filter-range" min="1" max="50" value="10" id="distanceRange">
            <div class="wn-distance-display">
                <span>1 km</span>
                <span id="distanceValue">10 km</span>
                <span>50 km</span>
            </div>
        </div>
    </div>

    <div class="wn-filters-actions">
        <button class="btn-wn-secondary" type="button">
            <i class="bi bi-x-lg"></i> Limpiar
        </button>
        <button class="btn-wn-primary" type="button">
            <i class="bi bi-search"></i> Aplicar
        </button>
    </div>
</aside>
