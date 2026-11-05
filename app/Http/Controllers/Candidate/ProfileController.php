<?php

namespace App\Http\Controllers\Candidate;

use App\Http\Controllers\Controller;
use App\Models\Aptitud;
use App\Models\Candidato;
use App\Models\Curso;
use App\Models\Experiencia;
use App\Models\Idioma;
use App\Models\NivelIdioma;
use App\Models\Titulo;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Perfil del candidato con datos REALES de la BD
     * (usuario + candidato + experiencia/titulos/aptitudes/idiomas/CV).
     */
    public function index(): View
    {
        $usuario = Auth::user();
        $candidato = $usuario->candidato;

        $perfil = $candidato->perfil;

        // Datos personales: solo los campos que existen en la BD.
        $profile = [
            'name' => $usuario->nombre,
            'initials' => SavedOfferController::initials($usuario->nombre ?? ''),
            'headline' => $perfil ? Str::limit($perfil, 120) : 'Candidato en Work.net',
            'email' => $usuario->email,
            'location' => $candidato->nacionalidad,
            'birthdate' => $candidato->fecha_nacimiento
                ? Carbon::parse($candidato->fecha_nacimiento)->format('d/m/Y')
                : null,
            'rawFnac' => $candidato->fecha_nacimiento,
            'genero' => $candidato->genero,
            'estadoLaboral' => $candidato->estado_laboral,
            'esEstudiante' => $candidato->es_estudiante,
            'universidad' => $candidato->universidad,
            'about' => $perfil,
        ];

        // Experiencia laboral (BD).
        $experiences = Experiencia::query()
            ->where('id_candidato', $candidato->id_candidato)
            ->orderByDesc('fecha_inicio')
            ->get()
            ->map(fn (Experiencia $e) => [
                'id' => $e->id_experiencia,
                'position' => $e->puesto,
                'company' => $e->empresa,
                'period' => $this->periodoLabel($e->fecha_inicio, $e->fecha_fin, (bool) $e->actual),
                'description' => $e->tareas,
                'ubicacion' => $e->ubicacion,
                'rawInicio' => $e->fecha_inicio,
                'rawFin' => $e->fecha_fin,
                'rawActual' => $e->actual,
                'rawTareas' => $e->tareas,
                'rawUbicacion' => $e->ubicacion,
            ])->all();

        // Formación académica (tabla titulo).
        $educations = Titulo::query()
            ->where('id_candidato', $candidato->id_candidato)
            ->orderByDesc('fecha_inicio')
            ->get()
            ->map(fn (Titulo $t) => [
                'id' => $t->id_titulo,
                'degree' => $t->nombre,
                'institution' => $t->institucion,
                'period' => $this->periodoLabel($t->fecha_inicio, $t->fecha_fin, false),
                'description' => $t->tipo,
                'rawInicio' => $t->fecha_inicio,
                'rawFin' => $t->fecha_fin,
                'rawTipo' => $t->tipo,
                'rawCertificado' => $t->certificado,
            ])->all();

        // Habilidades (pivote candidato_aptitud + catálogo aptitud).
        $skills = $candidato->aptitudes()
            ->orderBy('id_aptitud')
            ->get()
            ->map(fn (Aptitud $a) => [
                'id' => $a->id_aptitud,
                'name' => $a->nombre,
                'level' => $a->pivot->nivel ?: 'Intermedio',
            ])->all();

        // Idiomas (pivote candidato_idioma + catálogos).
        $languages = DB::table('candidato_idioma')
            ->join('idioma', 'idioma.id_idioma', '=', 'candidato_idioma.id_idioma')
            ->leftJoin('nivel_idioma', 'nivel_idioma.id_nivel', '=', 'candidato_idioma.id_nivel')
            ->where('candidato_idioma.id_candidato', $candidato->id_candidato)
            ->orderBy('idioma.id_idioma')
            ->get([
                'idioma.id_idioma as id',
                'idioma.nombre as name',
                'nivel_idioma.nombre as level',
            ])
            ->map(fn ($row) => [
                'id' => (int) $row->id,
                'name' => $row->name,
                'level' => $row->level ?? 'Por confirmar',
            ])->all();

        // Cursos (tables curso; aún sin sección propia, se computan en completitud).
        $cursosCount = Curso::query()->where('id_candidato', $candidato->id_candidato)->count();

        // CV real.
        $cv = null;
        if ($candidato->cv && Storage::disk('public')->exists($candidato->cv)) {
            $cv = [
                'path' => $candidato->cv,
                'filename' => basename($candidato->cv),
                'uploaded_at' => $candidato->cv_fecha_act
                    ? Carbon::parse($candidato->cv_fecha_act)->locale('es')->translatedFormat('d \d\e F \d\e Y')
                    : null,
                'size' => $this->tamanoLegible(Storage::disk('public')->size($candidato->cv)),
            ];
        } elseif ($candidato->cv) {
            // Referencia huérfana: se limpia al vuelo.
            $candidato->cv = null;
            $candidato->save();
        }

        // Catálogos para los modales.
        $nivelesIdioma = NivelIdioma::query()->orderBy('orden')->get();
        $estadosLaborales = ['Buscando oportunidades', 'Estudiando', 'Trabajando', 'Independiente', 'Sin actividad'];
        $generos = ['Femenino', 'Masculino', 'Otro', 'Prefiero no decir'];
        $nivelesAptitud = ['Básico', 'Intermedio', 'Avanzado'];

        $progress = static::progresoPerfil($candidato, $cursosCount);

        return view('candidate.profile', compact(
            'profile', 'experiences', 'educations', 'skills', 'languages',
            'cv', 'progress', 'nivelesIdioma', 'estadosLaborales', 'generos', 'nivelesAptitud'
        ));
    }

    // ============================================
    // Datos personales
    // ============================================

    public function update(Request $request): RedirectResponse
    {
        $usuario = Auth::user();
        $candidato = $usuario->candidato;

        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', 'unique:usuario,email,' . $usuario->id_usuario . ',id_usuario'],
            'fecha_nacimiento' => ['nullable', 'date', 'before:today'],
            'genero' => ['nullable', 'string', 'max:30'],
            'nacionalidad' => ['nullable', 'string', 'max:80'],
            'estado_laboral' => ['nullable', 'string', 'max:80'],
            'es_estudiante' => ['nullable', 'boolean'],
            'universidad' => ['nullable', 'string', 'max:120'],
            'perfil' => ['nullable', 'string', 'max:2000'],
        ]);

        $usuario->nombre = $validated['nombre'];
        $usuario->email = $validated['email'];
        $usuario->save();

        $candidato->fecha_nacimiento = $validated['fecha_nacimiento'];
        $candidato->genero = $validated['genero'];
        $candidato->nacionalidad = $validated['nacionalidad'];
        $candidato->universidad = $validated['universidad'];
        $candidato->estado_laboral = $validated['estado_laboral'];
        $candidato->es_estudiante = $request->boolean('es_estudiante');
        $candidato->perfil = $validated['perfil'];
        $candidato->save();

        return back()->with('success', 'Perfil actualizado correctamente.');
    }

    // ============================================
    // Situación laboral (solo esos 3 campos)
    // ============================================

    public function situacionUpdate(Request $request): RedirectResponse
    {
        $candidato = Auth::user()->candidato;

        $validated = $request->validate([
            'estado_laboral' => ['nullable', 'string', 'max:80'],
            'es_estudiante' => ['nullable', 'boolean'],
            'universidad' => ['nullable', 'string', 'max:120'],
        ]);

        $candidato->estado_laboral = $validated['estado_laboral'];
        $candidato->es_estudiante = $request->boolean('es_estudiante');
        $candidato->universidad = $validated['universidad'];
        $candidato->save();

        return back()->with('success', 'Situación laboral actualizada correctamente.');
    }

    // ============================================
    // Experiencia laboral (experiencia)
    // ============================================

    public function experienciaStore(Request $request): RedirectResponse
    {
        $datos = $this->validarExperiencia($request);

        Experiencia::query()->create([
            'id_candidato' => Auth::user()->candidato->id_candidato,
            ...$datos,
        ]);

        return back()->with('success', 'Experiencia agregada correctamente.');
    }

    public function experienciaUpdate(Request $request, int $idExperiencia): RedirectResponse
    {
        $experiencia = Experiencia::query()
            ->where('id_experiencia', $idExperiencia)
            ->where('id_candidato', Auth::user()->candidato->id_candidato)
            ->first();

        if (! $experiencia) {
            return back()->withErrors(['experiencia' => 'La experiencia no existe.']);
        }

        $experiencia->update($this->validarExperiencia($request));

        return back()->with('success', 'Experiencia actualizada correctamente.');
    }

    public function experienciaDestroy(int $idExperiencia): RedirectResponse
    {
        Experiencia::query()
            ->where('id_experiencia', $idExperiencia)
            ->where('id_candidato', Auth::user()->candidato->id_candidato)
            ->delete();

        return back()->with('success', 'Experiencia eliminada correctamente.');
    }

    private function validarExperiencia(Request $request): array
    {
        $validated = $request->validate([
            'puesto' => ['required', 'string', 'max:100'],
            'empresa' => ['required', 'string', 'max:100'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after:fecha_inicio'],
            'actual' => ['nullable', 'boolean'],
            'tareas' => ['nullable', 'string', 'max:2000'],
            'ubicacion' => ['nullable', 'string', 'max:120'],
        ]);

        // Si marca "actual", se ignora fecha_fin (y viceversa).
        if ($request->boolean('actual')) {
            $validated['fecha_fin'] = null;
        } elseif (! $validated['fecha_fin']) {
            $request->validate(['fecha_fin' => ['required', 'date', 'after:fecha_inicio']], ['fecha_fin.required' => 'Indicá la fecha de fin o marcá "Trabajo aquí actualmente".']);
        }

        return [
            'puesto' => $validated['puesto'],
            'empresa' => $validated['empresa'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
            'actual' => (int) $request->boolean('actual'),
            'tareas' => $validated['tareas'],
            'ubicacion' => $validated['ubicacion'],
        ];
    }

    // ============================================
    // Formación académica (titulo)
    // ============================================

    public function tituloStore(Request $request): RedirectResponse
    {
        Titulo::query()->create([
            'id_candidato' => Auth::user()->candidato->id_candidato,
            ...$this->validarTitulo($request),
        ]);

        return back()->with('success', 'Formación académica agregada correctamente.');
    }

    public function tituloUpdate(Request $request, int $idTitulo): RedirectResponse
    {
        $titulo = Titulo::query()
            ->where('id_titulo', $idTitulo)
            ->where('id_candidato', Auth::user()->candidato->id_candidato)
            ->first();

        if (! $titulo) {
            return back()->withErrors(['titulo' => 'El registro no existe.']);
        }

        $titulo->update($this->validarTitulo($request));

        return back()->with('success', 'Formación académica actualizada correctamente.');
    }

    public function tituloDestroy(int $idTitulo): RedirectResponse
    {
        Titulo::query()
            ->where('id_titulo', $idTitulo)
            ->where('id_candidato', Auth::user()->candidato->id_candidato)
            ->delete();

        return back()->with('success', 'Registro de formación eliminado correctamente.');
    }

    private function validarTitulo(Request $request): array
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'institucion' => ['required', 'string', 'max:120'],
            'tipo' => ['nullable', 'string', 'max:60'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['nullable', 'date', 'after:fecha_inicio'],
            'certificado' => ['nullable', 'boolean'],
        ]);

        return [
            'nombre' => $validated['nombre'],
            'institucion' => $validated['institucion'],
            'tipo' => $validated['tipo'],
            'fecha_inicio' => $validated['fecha_inicio'],
            'fecha_fin' => $validated['fecha_fin'],
            'certificado' => (int) $request->boolean('certificado'),
        ];
    }

    // ============================================
    // Habilidades (candidato_aptitud + catálogo)
    // ============================================

    public function habilidadStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'habilidad' => ['required', 'string', 'max:60'],
            'nivel' => ['nullable', 'in:Básico,Intermedio,Avanzado'],
        ]);

        $candidato = Auth::user()->candidato;

        $habilidad = ucfirst(mb_strtolower(trim($validated['habilidad'])));

        // Catálogo de aptitudes: existe (aunque cambien mayúsculas) → se usa; no existe → se crea.
        $aptitud = Aptitud::query()
            ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($habilidad)])
            ->first();

        if (! $aptitud) {
            $aptitud = Aptitud::query()->create([
                'nombre' => $habilidad,
                'tipo' => 'Habilidad',
                'activo' => true,
            ]);
        }

        if ($candidato->aptitudes()->where('aptitud.id_aptitud', $aptitud->id_aptitud)->exists()) {
            return back()->withErrors(['habilidad' => 'Ya tenés esa habilidad cargada.']);
        }

        $candidato->aptitudes()->attach($aptitud->id_aptitud, [
            'nivel' => $validated['nivel'] ?? 'Intermedio',
        ]);

        return back()->with('success', 'Habilidad agregada correctamente.');
    }

    public function habilidadUpdate(Request $request, int $idAptitud): RedirectResponse
    {
        $validated = $request->validate(['nivel' => ['required', 'in:Básico,Intermedio,Avanzado']]);

        $candidato = Auth::user()->candidato;

        if (! $candidato->aptitudes()->where('aptitud.id_aptitud', $idAptitud)->exists()) {
            return back()->withErrors(['habilidad' => 'La habilidad no existe.']);
        }

        $candidato->aptitudes()->updateExistingPivot($idAptitud, ['nivel' => $validated['nivel']]);

        return back()->with('success', 'Nivel de la habilidad actualizado.');
    }

    public function habilidadDestroy(int $idAptitud): RedirectResponse
    {
        Auth::user()->candidato->aptitudes()->detach($idAptitud);

        return back()->with('success', 'Habilidad eliminada correctamente.');
    }

    // ============================================
    // Idiomas (candidato_idioma + catálogos)
    // ============================================

    public function idiomaStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'idioma' => ['required', 'string', 'max:60'],
            'id_nivel' => ['required', 'exists:nivel_idioma,id_nivel'],
        ]);

        $candidato = Auth::user()->candidato;

        $nombreIdioma = ucfirst(mb_strtolower(trim($validated['idioma'])));
        $nombreLimpio = preg_replace('/\W+/', '', mb_strtolower($nombreIdioma)) ?? '';
        $codigoIso = substr($nombreLimpio, 0, 3) ?: 'xx';

        $idioma = Idioma::query()
            ->whereRaw('LOWER(nombre) = ?', [mb_strtolower($nombreIdioma)])
            ->first();

        if (! $idioma) {
            $idioma = Idioma::query()->create([
                'nombre' => $nombreIdioma,
                'codigo_iso' => $codigoIso,
            ]);
        }

        if ($candidato->idiomas()->where('idioma.id_idioma', $idioma->id_idioma)->exists()) {
            return back()->withErrors(['idioma' => 'Ya tenés ese idioma cargado.']);
        }

        $candidato->idiomas()->attach($idioma->id_idioma, ['id_nivel' => $validated['id_nivel']]);

        return back()->with('success', 'Idioma agregado correctamente.');
    }

    public function idiomaDestroy(int $idIdioma): RedirectResponse
    {
        Auth::user()->candidato->idiomas()->detach($idIdioma);

        return back()->with('success', 'Idioma eliminado correctamente.');
    }

    // ============================================
    // CV
    // ============================================

    public function cvStore(Request $request): RedirectResponse
    {
        $request->validate([
            'cv_file' => ['required', 'file', 'max:4096', 'mimes:pdf,doc,docx'],
        ]);

        $candidato = Auth::user()->candidato;

        // Elimina el archivo anterior si existía.
        if ($candidato->cv) {
            Storage::disk('public')->delete($candidato->cv);
        }

        $path = $request->file('cv_file')->store('cv/candidato-' . $candidato->id_candidato, 'public');

        $candidato->cv = $path;
        $candidato->cv_fecha_act = now();
        $candidato->save();

        return back()->with('success', 'CV cargado correctamente.');
    }

    public function cvDestroy(): RedirectResponse
    {
        $candidato = Auth::user()->candidato;

        if ($candidato->cv) {
            Storage::disk('public')->delete($candidato->cv);
            $candidato->cv = null;
            $candidato->save();
        }

        return back()->with('success', 'CV eliminado correctamente.');
    }

    public function cvDownload()
    {
        $candidato = Auth::user()->candidato;

        if (! $candidato->cv || ! Storage::disk('public')->exists($candidato->cv)) {
            return back()->withErrors(['cv' => 'Todavía no cargaste tu CV.']);
        }

        return Storage::disk('public')->download($candidato->cv);
    }

    // ============================================
    // Helpers
    // ============================================

    private function periodoLabel(?string $inicio, ?string $fin, bool $actual): string
    {
        $formato = 'M Y';
        $desde = $inicio ? Carbon::parse($inicio)->locale('es')->translatedFormat($formato) : '—';
        $hasta = $actual || ! $fin ? 'Actualidad' : Carbon::parse($fin)->locale('es')->translatedFormat($formato);

        return "$desde - $hasta";
    }

    private function tamanoLegible(int $bytes): string
    {
        return number_format($bytes / 1024, 0) . ' KB';
    }

    /**
     * Completitud del perfil (misma fórmula en dashboard y perfil).
     * Se recalcula con datos reales de la BD.
     */
    public static function progresoPerfil(?Candidato $candidato, int $cursosCount = 0): array
    {
        if (! $candidato) {
            return ['percentage' => 0, 'checklist' => [], 'missing' => []];
        }

        $usuario = Auth::user();
        $candidato->loadCount(['experiencias', 'titulos', 'aptitudes', 'idiomas']);

        $items = [
            ['label' => 'Datos personales', 'done' => (bool) ($usuario?->nombre && $usuario?->email) || (bool) $candidato->fecha_nacimiento],
            ['label' => 'Sobre mí', 'done' => (bool) $candidato->perfil],
            ['label' => 'Experiencia laboral', 'done' => $candidato->experiencias_count > 0],
            ['label' => 'Educación', 'done' => $candidato->titulos_count > 0],
            ['label' => 'Habilidades e idiomas', 'done' => $candidato->aptitudes_count > 0 || $candidato->idiomas_count > 0],
            ['label' => 'Curriculum (CV)', 'done' => (bool) $candidato->cv],
        ];

        $done = count(array_filter($items, fn ($item) => $item['done']));
        $missing = array_values(array_map(fn ($item) => $item['label'], array_filter($items, fn ($item) => ! $item['done'])));
        $cursosCount = $cursosCount ?: Curso::query()->where('id_candidato', $candidato->id_candidato)->count();

        return [
            'percentage' => (int) round($done / count($items) * 100),
            'checklist' => $items,
            'missing' => $missing,
        ];
    }
}
