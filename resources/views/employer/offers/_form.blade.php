{{-- Partial compartido entre crear y editar oferta --}}
{{-- Estructura idéntica al prototipo resources/views/prototypes/oferta-publicar.html --}}
{{-- SIN apartado de Dirección: la ubicación de toda oferta es la --}}
{{-- de la sede registrada en "Mi empresa" (regla del TFG). --}}
@php($offer = $offer ?? [])

<form method="POST" action="{{ isset($offer['id']) ? route('employer.offers.update', $offer['id']) : route('employer.offers.store') }}">
    @csrf
    @if(isset($offer['id']))
        @method('PUT')
    @endif

    <!-- SECCIÓN 1: Información del puesto -->
    <section class="wn-section">
        <div class="wn-section-header">
            <div class="wn-section-icon"><i class="bi bi-briefcase"></i></div>
            <div>
                <h2 class="wn-section-title">Información del puesto</h2>
                <p class="wn-section-subtitle">Datos principales que verán los candidatos.</p>
            </div>
        </div>

        <div class="wn-form-group">
            <label class="wn-label">Título del puesto <span class="required">*</span></label>
            <input type="text" name="title" class="wn-input" placeholder="Ej: Desarrollador Full Stack Laravel" value="{{ old('title', $offer['title'] ?? '') }}" required>
            <span class="wn-hint">Sé específico. Un buen título atrae más candidatos.</span>
        </div>

        <div class="wn-form-row">
            <div class="wn-form-group">
                <label class="wn-label">Área laboral <span class="required">*</span></label>
                <select name="area" class="wn-select" required>
                    <option value="">Seleccionar área</option>
                    <option value="1" @selected((string) old('area', $offer['area'] ?? '') === '1')>Informática / TI</option>
                    <option value="2" @selected((string) old('area', $offer['area'] ?? '') === '2')>Diseño</option>
                    <option value="3" @selected((string) old('area', $offer['area'] ?? '') === '3')>Marketing</option>
                    <option value="4" @selected((string) old('area', $offer['area'] ?? '') === '4')>Administración</option>
                </select>
            </div>
            <div class="wn-form-group">
                <label class="wn-label">Categoría / Nivel <span class="required">*</span></label>
                <select name="level" class="wn-select" required>
                    <option value="">Seleccionar nivel</option>
                    <option value="junior" @selected(old('level', $offer['level'] ?? '') === 'junior')>Junior</option>
                    <option value="semi-senior" @selected(old('level', $offer['level'] ?? '') === 'semi-senior')>Semi-Senior</option>
                    <option value="senior" @selected(old('level', $offer['level'] ?? '') === 'senior')>Senior</option>
                    <option value="lead" @selected(old('level', $offer['level'] ?? '') === 'lead')>Lead / Tech Lead</option>
                </select>
            </div>
        </div>

        <div class="wn-form-group">
            <label class="wn-label">Descripción del puesto <span class="required">*</span></label>
            <textarea name="description" class="wn-textarea" rows="5" placeholder="Describe las responsabilidades principales, el contexto del rol y las tareas diarias..." required>{{ old('description', $offer['description'] ?? '') }}</textarea>
            <span class="wn-hint">Mínimo 100 caracteres. Cuanto más claro, mejor.</span>
        </div>
    </section>

    <!-- SECCIÓN 2: Modalidad y contratación -->
    <section class="wn-section">
        <div class="wn-section-header">
            <div class="wn-section-icon"><i class="bi bi-laptop"></i></div>
            <div>
                <h2 class="wn-section-title">Modalidad y contratación</h2>
                <p class="wn-section-subtitle">Definí cómo se trabajará y bajo qué condiciones.</p>
            </div>
        </div>

        <div class="wn-form-group">
            <label class="wn-label">Modalidad de trabajo <span class="required">*</span></label>
            <div class="wn-radio-cards">
                <div class="wn-radio-card">
                    <input type="radio" name="modality" id="mod-presencial" value="presencial" @checked(old('modality', $offer['modality'] ?? 'presencial') === 'presencial')>
                    <label for="mod-presencial">
                        <i class="bi bi-building"></i>
                        <span>Presencial</span>
                    </label>
                </div>
                <div class="wn-radio-card">
                    <input type="radio" name="modality" id="mod-remoto" value="remoto" @checked(old('modality', $offer['modality'] ?? '') === 'remoto')>
                    <label for="mod-remoto">
                        <i class="bi bi-house"></i>
                        <span>Remoto</span>
                    </label>
                </div>
                <div class="wn-radio-card">
                    <input type="radio" name="modality" id="mod-hibrido" value="hibrido" @checked(old('modality', $offer['modality'] ?? '') === 'hibrido')>
                    <label for="mod-hibrido">
                        <i class="bi bi-arrow-left-right"></i>
                        <span>Híbrido</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="wn-form-row three">
            <div class="wn-form-group">
                <label class="wn-label">Tipo de contrato <span class="required">*</span></label>
                <select name="contract" class="wn-select" required>
                    <option value="">Seleccionar</option>
                    <option value="tiempo-completo" @selected(old('contract', $offer['contract'] ?? '') === 'tiempo-completo')>Tiempo completo</option>
                    <option value="medio-tiempo" @selected(old('contract', $offer['contract'] ?? '') === 'medio-tiempo')>Medio tiempo</option>
                    <option value="pasantia" @selected(old('contract', $offer['contract'] ?? '') === 'pasantia')>Pasantía</option>
                    <option value="contrato" @selected(old('contract', $offer['contract'] ?? '') === 'contrato')>Por contrato</option>
                    <option value="freelance" @selected(old('contract', $offer['contract'] ?? '') === 'freelance')>Freelance</option>
                </select>
            </div>
            <div class="wn-form-group">
                <label class="wn-label">Jornada <span class="required">*</span></label>
                <select name="schedule" class="wn-select" required>
                    <option value="">Seleccionar</option>
                    <option value="completa" @selected(old('schedule', $offer['schedule'] ?? '') === 'completa')>Jornada completa</option>
                    <option value="parcial" @selected(old('schedule', $offer['schedule'] ?? '') === 'parcial')>Jornada parcial</option>
                    <option value="flexible" @selected(old('schedule', $offer['schedule'] ?? '') === 'flexible')>Horario flexible</option>
                </select>
            </div>
            <div class="wn-form-group">
                <label class="wn-label">Fecha límite <span class="required">*</span></label>
                <input type="date" name="deadline" class="wn-input" value="{{ old('deadline', $offer['deadline'] ?? '') }}" required>
            </div>
        </div>

        <div class="wn-form-row">
            <div class="wn-form-group">
                <label class="wn-label">Remuneración mínima (Gs.) <span class="optional">(opcional)</span></label>
                <input type="text" name="salary_min" class="wn-input" placeholder="Ej: 7.000.000" value="{{ old('salary_min', $offer['salaryMin'] ?? '') }}">
            </div>
            <div class="wn-form-group">
                <label class="wn-label">Remuneración máxima (Gs.) <span class="optional">(opcional)</span></label>
                <input type="text" name="salary_max" class="wn-input" placeholder="Ej: 10.000.000" value="{{ old('salary_max', $offer['salaryMax'] ?? '') }}">
            </div>
        </div>
    </section>

    <!-- SECCIÓN 3: Requisitos -->
    <section class="wn-section">
        <div class="wn-section-header">
            <div class="wn-section-icon"><i class="bi bi-list-check"></i></div>
            <div>
                <h2 class="wn-section-title">Requisitos y habilidades</h2>
                <p class="wn-section-subtitle">Lo que buscás en el candidato ideal.</p>
            </div>
        </div>

        <div class="wn-form-group">
            <label class="wn-label">Experiencia requerida <span class="required">*</span></label>
            <textarea name="experience" class="wn-textarea" rows="3" placeholder="Ej: 3+ años de experiencia en desarrollo backend con PHP y Laravel..." required>{{ old('experience', $offer['experience'] ?? '') }}</textarea>
        </div>

        <div class="wn-form-group">
            <label class="wn-label">Formación académica <span class="optional">(opcional)</span></label>
            <textarea name="education" class="wn-textarea" rows="2" placeholder="Ej: Licenciatura en Análisis de Sistemas, Ingeniería Informática o similar...">{{ old('education', $offer['education'] ?? '') }}</textarea>
        </div>

        <div class="wn-form-group">
            <label class="wn-label">Habilidades técnicas <span class="required">*</span></label>
            <textarea name="skills" class="wn-textarea" rows="3" placeholder="Ej: PHP, Laravel, MySQL, PostgreSQL, Git, Docker, APIs REST..." required>{{ old('skills', $offer['skills'] ?? '') }}</textarea>
            <span class="wn-hint">Separá las habilidades con comas.</span>
        </div>

        <div class="wn-form-group">
            <label class="wn-label">Habilidades blandas <span class="optional">(opcional)</span></label>
            <textarea name="soft_skills" class="wn-textarea" rows="2" placeholder="Ej: Trabajo en equipo, comunicación efectiva, proactividad...">{{ old('soft_skills', $offer['softSkills'] ?? '') }}</textarea>
        </div>
    </section>

    <!-- SECCIÓN 4: Beneficios -->
    <section class="wn-section">
        <div class="wn-section-header">
            <div class="wn-section-icon"><i class="bi bi-star"></i></div>
            <div>
                <h2 class="wn-section-title">Beneficios adicionales</h2>
                <p class="wn-section-subtitle">Seleccioná los que aplican a esta oferta.</p>
            </div>
        </div>

        @php($selectedBenefits = old('benefits', $offer['benefits'] ?? []))
        <div class="wn-chips">
            <div class="wn-chip">
                <input type="checkbox" name="benefits[]" id="b-seguro" value="seguro" @checked(in_array('seguro', $selectedBenefits))>
                <label for="b-seguro"><i class="bi bi-heart-pulse"></i> Seguro médico</label>
            </div>
            <div class="wn-chip">
                <input type="checkbox" name="benefits[]" id="b-capacitacion" value="capacitacion" @checked(in_array('capacitacion', $selectedBenefits))>
                <label for="b-capacitacion"><i class="bi bi-mortarboard"></i> Capacitaciones</label>
            </div>
            <div class="wn-chip">
                <input type="checkbox" name="benefits[]" id="b-horario" value="horario" @checked(in_array('horario', $selectedBenefits))>
                <label for="b-horario"><i class="bi bi-clock"></i> Horario flexible</label>
            </div>
            <div class="wn-chip">
                <input type="checkbox" name="benefits[]" id="b-almuerzo" value="almuerzo" @checked(in_array('almuerzo', $selectedBenefits))>
                <label for="b-almuerzo"><i class="bi bi-cup-hot"></i> Almuerzo</label>
            </div>
            <div class="wn-chip">
                <input type="checkbox" name="benefits[]" id="b-transporte" value="transporte" @checked(in_array('transporte', $selectedBenefits))>
                <label for="b-transporte"><i class="bi bi-bus-front"></i> Transporte</label>
            </div>
            <div class="wn-chip">
                <input type="checkbox" name="benefits[]" id="b-bonos" value="bonos" @checked(in_array('bonos', $selectedBenefits))>
                <label for="b-bonos"><i class="bi bi-cash-coin"></i> Bonos</label>
            </div>
            <div class="wn-chip">
                <input type="checkbox" name="benefits[]" id="b-homeoffice" value="homeoffice" @checked(in_array('homeoffice', $selectedBenefits))>
                <label for="b-homeoffice"><i class="bi bi-house-door"></i> Home office</label>
            </div>
        </div>

        <div class="wn-info-box" style="margin-top: 16px;">
            <i class="bi bi-geo-alt-fill"></i>
            <div class="wn-info-box-text">
                <strong>Ubicación:</strong> la oferta se mostrará en el mapa con la dirección de tu sede
                ({{ $ofertaSede?->ubicacion?->direccion ?? 'sin sede cargada' }}, {{ $ofertaSede?->ubicacion?->ciudad ?? '—' }}).
                Podés cambiarla en <strong>"Mi empresa"</strong>.
            </div>
        </div>
    </section>

    <!-- ACCIONES -->
    <div class="wn-form-actions">
        <a href="{{ route('employer.offers.index') }}" class="btn-wn-secondary">
            <i class="bi bi-x-lg"></i> Cancelar
        </a>
        <div class="wn-form-actions-right">
            <button type="submit" class="btn-wn-primary">
                <i class="bi bi-send"></i> {{ isset($offer['id']) ? 'Guardar cambios' : 'Publicar oferta' }}
            </button>
        </div>
    </div>
</form>
