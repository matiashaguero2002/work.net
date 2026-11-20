<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Models\BeneficioOferta;
use App\Models\Empleador;
use App\Models\Oferta;
use App\Models\RequisitoOferta;
use App\Models\Sede;
use App\Services\MatchPostulante;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Módulo "Mis ofertas" del empleador con datos REALES de la BD.
 * La ubicación de cada oferta es SIEMPRE la de la sede del
 * perfil de empresa (por regla de diseño del TFG).
 */
class OfferController extends Controller
{
    public function index(): View
    {
        $empleador = Auth::user()->empleador;

        if (!$empleador) {
            abort(403, 'Tu usuario no tiene un perfil de empleador asociado.');
        }

        $ofertas = Oferta::query()
            ->where('id_empleador', $empleador->id_empleador)
            ->with(['ubicacion', 'modalidad', 'tipoContrato'])
            ->withCount('postulaciones')
            ->orderByDesc('fecha_publicacion')
            ->get();

        $offers = $ofertas->map(fn (Oferta $oferta) => $this->presentacionIndex($oferta))->values()->all();

        $stats = [
            'total' => count($offers),
            'active' => $ofertas->where('id_estado_oferta', 1)->count(),
            'paused' => $ofertas->where('id_estado_oferta', 2)->count(),
            'closed' => $ofertas->where('id_estado_oferta', 3)->count(),
        ];

        return view('employer.offers.index', compact('offers', 'stats'));
    }

    public function show(int $id): View
    {
        $oferta = $this->ofertaPropia($id);
        if (! $oferta) {
            abort(404);
        }

        $oferta->load(['ubicacion', 'modalidad', 'tipoContrato', 'areaLaboral']);

        $requisitos = $oferta->requisitos()->get();
        $skills = $requisitos->where('id_tipo_req', 1)->values();
        $softSkills = $requisitos->where('id_tipo_req', 4)->values();
        $experiencia = optional($requisitos->firstWhere('id_tipo_req', 3))->descripcion;
        $formacion = optional($requisitos->firstWhere('id_tipo_req', 2))->descripcion;

        $beneficios = $oferta->beneficios()->get()
            ->map(fn ($b) => ['icon' => static::iconoBeneficio($b->descripcion), 'label' => $b->descripcion])
            ->all();

        $postulaciones = $oferta->postulaciones()
            ->orderByDesc('fecha_postulacion')
            ->get();

        $recentApplicants = $postulaciones
            ->take(3)
            ->map(function ($postulacion) use ($oferta) {
                $candidato = $postulacion->candidato()->with('usuario')->first();

                return [
                    'id' => $postulacion->id_postulacion,
                    'initials' => \App\Http\Controllers\Candidate\SavedOfferController::initials($candidato?->usuario->nombre ?? ''),
                    'name' => $candidato?->usuario->nombre ?? '—',
                    'date' => Carbon::parse($postulacion->fecha_postulacion ?? now())
                        ->locale('es')->diffForHumans(),
                    'match' => MatchPostulante::puntaje($candidato, $oferta),
                ];
            })
            ->all();

        $diasRestantes = null;
        if ($oferta->fecha_limite && (int) $oferta->id_estado_oferta === 1) {
            // ceil: si faltan 7.08 días, quedan 8 días de margen (entero, sin decimales).
            $diasRestantes = max(0, (int) ceil(now()->diffInDays(Carbon::parse($oferta->fecha_limite), false)));
        }

        $ofertaArray = [
            'id' => $oferta->id_oferta,
            'title' => $oferta->titulo,
            'logo' => static::initials($oferta->titulo),
            'company' => $oferta->empleador->nombre_empresa ?? '—',
            'publishedLine' => 'Publicada el ' . Carbon::parse($oferta->fecha_publicacion ?? now())
                ->locale('es')->translatedFormat('j \d\e F \d\e Y'),
            'status' => $this->estadoSlug((int) $oferta->id_estado_oferta),
            'statusLabel' => $this->estadoLabel((int) $oferta->id_estado_oferta),
            'location' => $oferta->ubicacion?->ciudad ?? '—',
            'address' => trim(($oferta->ubicacion?->direccion ?? '') . ', ' . ($oferta->ubicacion?->ciudad ?? '')),
            'coords' => [
                (float) ($oferta->ubicacion?->latitud ?? 0),
                (float) ($oferta->ubicacion?->longitud ?? 0),
            ],
            'modality' => $oferta->modalidad?->nombre ?? '—',
            'modalityIcon' => static::iconoModalidad($oferta->modalidad?->nombre),
            'contractType' => $oferta->tipoContrato?->nombre ?? '—',
            'description' => $oferta->descripcion,
            'experience' => $experiencia ?? '—',
            'education' => $formacion ?? '—',
            'requirements' => $skills->pluck('descripcion')->all(),
            'softSkills' => $softSkills->pluck('descripcion')->all(),
            'benefits' => $beneficios,
            'salary' => $this->remuneracionLabel($oferta),
            'stats' => [
                ['icon' => 'bi-inbox-fill', 'label' => 'Postulantes', 'value' => $postulaciones->count()],
                ['icon' => 'bi-eye-fill', 'label' => 'Vistas', 'value' => (int) ($oferta->visualizaciones ?? 0)],
                ['icon' => 'bi-bookmark-fill', 'label' => 'Guardadas', 'value' => $oferta->guardadasPor()->count()],
                ['icon' => 'bi-hourglass-split', 'label' => 'Días restantes', 'value' => $diasRestantes !== null ? $diasRestantes : '—'],
            ],
            'applicantsCount' => $postulaciones->count(),
        ];

        return view('employer.offers.show', ['offer' => $ofertaArray, 'recentApplicants' => $recentApplicants, 'estadoOferta' => (int) $oferta->id_estado_oferta]);
    }

    public function create(): View
    {
        return view('employer.offers.create', ['ofertaSede' => $this->sedeDelEmpleador()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $empleador = Auth::user()->empleador;

        $validated = $this->validar($request);

        // La ubicación es la de la sede registrada en Mi empresa.
        $sede = $this->sedeDelEmpleador();
        if (! $sede) {
            return redirect()->route('employer.company.profile')
                ->withErrors(['sede' => 'Primero cargá la ubicación de tu sede en "Mi empresa".']);
        }

        $oferta = DB::transaction(function () use ($validated, $empleador, $sede) {
            $idOferta = DB::table('oferta')->insertGetId([
                'id_empleador' => $empleador->id_empleador,
                'id_ubicacion' => $sede->id_ubicacion,
                'id_area' => $validated['area'],
                'id_modalidad' => static::idModalidad($validated['modality']),
                'id_tipo_contrato' => static::idContrato($validated['contract']),
                'id_estado_oferta' => 1, // Activa
                'titulo' => $validated['title'],
                'descripcion' => $validated['description'],
                'requisitos' => static::textoRequisitos($validated),
                'forma_postulacion' => 'Postulá por Work.net con tu CV actualizado',
                'remuneracion_min' => $validated['remMin'],
                'remuneracion_max' => $validated['remMax'],
                'mostrar_remuneracion' => $validated['remMin'] !== null || $validated['remMax'] !== null,
                'beneficios' => implode(', ', $validated['beneficiosLabel']),
                'fecha_publicacion' => now(),
                'fecha_limite' => $validated['deadline'],
                'vacantes' => 1,
                'visualizaciones' => 0,
            ], 'id_oferta');

            static::guardarRequisitos($idOferta, $validated);
            static::guardarBeneficios($idOferta, $validated['beneficiosLabel']);

            return Oferta::query()->find($idOferta);
        });

        return redirect()->route('employer.offers.show', $oferta->id_oferta)
            ->with('success', '¡Oferta publicada correctamente! Ya aparece en el mapa de ofertas.');
    }

    public function edit(int $id): View
    {
        $oferta = $this->ofertaPropia($id);
        if (! $oferta) {
            abort(404);
        }

        $oferta->load(['ubicacion', 'modalidad', 'tipoContrato']);

        $requisitos = $oferta->requisitos()->get();
        $skills = $requisitos->where('id_tipo_req', 1)->pluck('descripcion')->implode(', ');
        $softSkills = $requisitos->where('id_tipo_req', 4)->pluck('descripcion')->implode(', ');
        $experiencia = optional($requisitos->firstWhere('id_tipo_req', 3))->descripcion ?? '';
        $formacion = optional($requisitos->firstWhere('id_tipo_req', 2))->descripcion ?? '';

        // Nivel y jornada quedaron guardados como líneas del texto de requisitos.
        preg_match('/^Nivel:\s*(.+)$/m', $oferta->requisitos ?? '', $mNivel);
        preg_match('/^Jornada:\s*(.+)$/m', $oferta->requisitos ?? '', $mJornada);

        $beneficios = $oferta->beneficios()->get()->pluck('descripcion')->all();

        $offer = [
            'id' => $oferta->id_oferta,
            'title' => $oferta->titulo,
            'area' => (string) $oferta->id_area,
            'level' => trim(mb_strtolower($mNivel[1] ?? '')),
            'description' => $oferta->descripcion,
            'modality' => static::slugModalidad($oferta->modalidad?->nombre),
            'contract' => static::slugContrato($oferta->tipoContrato?->nombre),
            'schedule' => trim(mb_strtolower($mJornada[1] ?? '')),
            'deadline' => $oferta->fecha_limite ? Carbon::parse($oferta->fecha_limite)->format('Y-m-d') : '',
            'salaryMin' => $oferta->remuneracion_min !== null ? number_format((float) $oferta->remuneracion_min, 0, ',', '.') : '',
            'salaryMax' => $oferta->remuneracion_max !== null ? number_format((float) $oferta->remuneracion_max, 0, ',', '.') : '',
            'experience' => $experiencia,
            'education' => $formacion,
            'skills' => $skills,
            'softSkills' => $softSkills,
            'benefits' => array_map(fn ($b) => $this->slugBeneficio($b), $beneficios),
        ];

        return view('employer.offers.edit', ['offer' => $offer, 'ofertaSede' => $this->sedeDelEmpleador()]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $oferta = $this->ofertaPropia($id);
        if (! $oferta) {
            abort(404);
        }

        $validated = $this->validar($request);

        DB::transaction(function () use ($validated, $oferta) {
            $oferta->titulo = $validated['title'];
            $oferta->descripcion = $validated['description'];
            $oferta->requisitos = static::textoRequisitos($validated);
            $oferta->id_area = $validated['area'];
            $oferta->id_modalidad = static::idModalidad($validated['modality']);
            $oferta->id_tipo_contrato = static::idContrato($validated['contract']);
            $oferta->remuneracion_min = $validated['remMin'];
            $oferta->remuneracion_max = $validated['remMax'];
            $oferta->mostrar_remuneracion = $validated['remMin'] !== null || $validated['remMax'] !== null;
            $oferta->beneficios = implode(', ', $validated['beneficiosLabel']);
            $oferta->fecha_limite = $validated['deadline'];
            $oferta->save();

            // Reemplazo los requisitos y beneficios estructurados.
            RequisitoOferta::query()->where('id_oferta', $oferta->id_oferta)->delete();
            BeneficioOferta::query()->where('id_oferta', $oferta->id_oferta)->delete();
            static::guardarRequisitos($oferta->id_oferta, $validated);
            static::guardarBeneficios($oferta->id_oferta, $validated['beneficiosLabel']);
        });

        return redirect()->route('employer.offers.show', $oferta->id_oferta)
            ->with('success', 'Oferta actualizada correctamente.');
    }

    // ============================================
    // Acciones de las tarjetas: pausar / reactivar / cerrar
    // ============================================

    public function pause(int $id): RedirectResponse
    {
        $oferta = $this->ofertaPropia($id);
        if ($oferta && (int) $oferta->id_estado_oferta === 1) {
            $oferta->id_estado_oferta = 2;
            $oferta->save();

            return back()->with('success', 'Oferta pausada. Deja de recibir postulaciones hasta que la reactives.');
        }

        return back()->withErrors(['oferta' => 'Solo las ofertas activas se pueden pausar.']);
    }

    public function reactivate(int $id): RedirectResponse
    {
        $oferta = $this->ofertaPropia($id);
        if ($oferta && (int) $oferta->id_estado_oferta === 2) {
            $oferta->id_estado_oferta = 1;
            $oferta->fecha_cierre = null;
            $oferta->save();

            return back()->with('success', 'Oferta reactivada. Vuelve a aceptar postulaciones.');
        }

        return back()->withErrors(['oferta' => 'Solo las ofertas pausadas se pueden reactivar.']);
    }

    public function close(int $id): RedirectResponse
    {
        $oferta = $this->ofertaPropia($id);
        if ($oferta && (int) $oferta->id_estado_oferta !== 3) {
            $oferta->id_estado_oferta = 3;
            $oferta->fecha_cierre = now();
            $oferta->save();

            return back()->with('success', 'Oferta cerrada. Ya no aparece en el mapa ni acepta postulaciones.');
        }

        return back()->withErrors(['oferta' => 'La oferta ya está cerrada.']);
    }

    public function duplicate(int $id): RedirectResponse
    {
        $origen = $this->ofertaPropia($id);
        if (! $origen) {
            abort(404);
        }

        $nueva = DB::transaction(function () use ($origen) {
            $idOferta = DB::table('oferta')->insertGetId([
                'id_empleador' => $origen->id_empleador,
                'id_ubicacion' => $origen->id_ubicacion,
                'id_area' => $origen->id_area,
                'id_modalidad' => $origen->id_modalidad,
                'id_tipo_contrato' => $origen->id_tipo_contrato,
                'id_estado_oferta' => 1, // La copia nace activa, editable a gusto.
                'titulo' => $origen->titulo . ' (copia)',
                'descripcion' => $origen->descripcion,
                'requisitos' => $origen->requisitos,
                'forma_postulacion' => $origen->forma_postulacion,
                'remuneracion_min' => $origen->remuneracion_min,
                'remuneracion_max' => $origen->remuneracion_max,
                'mostrar_remuneracion' => $origen->mostrar_remuneracion,
                'beneficios' => $origen->beneficios,
                'fecha_publicacion' => now(),
                'fecha_limite' => now()->modify('+30 days'),
                'vacantes' => $origen->vacantes ?? 1,
                'visualizaciones' => 0,
            ], 'id_oferta');

            foreach ($origen->requisitos()->get() as $req) {
                DB::table('requisito_oferta')->insert([
                    'id_oferta' => $idOferta, 'id_tipo_req' => $req->id_tipo_req,
                    'descripcion' => $req->descripcion, 'obligatorio' => $req->obligatorio,
                ]);
            }
            foreach ($origen->beneficios()->get() as $ben) {
                DB::table('beneficio_oferta')->insert([
                    'id_oferta' => $idOferta, 'descripcion' => $ben->descripcion,
                ]);
            }

            return Oferta::query()->find($idOferta);
        });

        return redirect()->route('employer.offers.edit', $nueva->id_oferta)
            ->with('success', 'Oferta duplicada. Revisá los datos y publicala cuando quieras.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $oferta = $this->ofertaPropia($id);
        if (! $oferta) {
            abort(404);
        }

        if ($oferta->postulaciones()->exists()) {
            return redirect()->route('employer.offers.index')
                ->withErrors(['oferta' => 'No se puede eliminar una oferta con postulaciones. Cerrá la oferta en su lugar.']);
        }

        RequisitoOferta::query()->where('id_oferta', $oferta->id_oferta)->delete();
        BeneficioOferta::query()->where('id_oferta', $oferta->id_oferta)->delete();
        $oferta->delete();

        return redirect()->route('employer.offers.index')->with('success', 'Oferta eliminada.');
    }

    // ============================================
    // Helpers compartidos (también usados por el dashboard)
    // ============================================

    public static function idModalidad(?string $slug): ?int
    {
        return match ($slug) {
            'presencial' => 1,
            'remoto' => 2,
            'hibrido' => 3,
            default => null,
        };
    }

    public static function slugModalidad(?string $nombre): string
    {
        return match ($nombre) {
            'Presencial' => 'presencial',
            'Remoto' => 'remoto',
            'Híbrido' => 'hibrido',
            default => 'presencial',
        };
    }

    public static function idContrato(?string $slug): ?int
    {
        return match ($slug) {
            'tiempo-completo' => 1,
            'medio-tiempo' => 2,
            'pasantia' => 3,
            'contrato' => 4,
            'freelance' => 5,
            default => null,
        };
    }

    public static function slugContrato(?string $nombre): string
    {
        return match ($nombre) {
            'Medio tiempo' => 'medio-tiempo',
            'Pasantía' => 'pasantia',
            'Por contrato' => 'contrato',
            'Freelance' => 'freelance',
            default => 'tiempo-completo',
        };
    }

    public static function iconoModalidad(?string $nombre): string
    {
        return match ($nombre) {
            'Remoto' => 'house',
            'Híbrido' => 'arrow-left-right',
            default => 'building',
        };
    }

    public static function estadoSlug(int $estado): string
    {
        return match ($estado) {
            2 => 'paused',
            3 => 'closed',
            default => 'active',
        };
    }

    public static function estadoLabel(int $estado): string
    {
        return match ($estado) {
            2 => 'Pausada',
            3 => 'Cerrada',
            default => 'Activa',
        };
    }

    public static function initials(string $titulo): string
    {
        // Dos iniciales ignorando artículos ("Desarrollador Laravel Senior" → DL).
        $palabras = preg_split('/\s+/', trim($titulo)) ?: [];
        $ignorar = ['de', 'la', 'el', 'y', 'en', 'del', 'a'];
        $iniciales = '';
        foreach ($palabras as $palabra) {
            $palabra = trim($palabra);
            if ($palabra === '' || in_array(mb_strtolower($palabra), $ignorar, true)) continue;
            $iniciales .= mb_strtoupper(mb_substr($palabra, 0, 1));
            if (mb_strlen($iniciales) >= 2) break;
        }

        return $iniciales !== '' ? $iniciales : 'OF';
    }

    public static function iconoBeneficio(string $label): string
    {
        $l = mb_strtolower($label);
        foreach ([
            'heart-pulse' => ['seguro', 'salud'],
            'mortarboard' => ['capacitac', 'formac'],
            'clock' => ['horario'],
            'cup-hot' => ['almuerzo', 'café', 'cafe'],
            'bus-front' => ['transporte'],
            'cash-coin' => ['bono', 'pago'],
            'house-door' => ['home office', 'remoto'],
        ] as $icono => $claves) {
            foreach ($claves as $clave) {
                if (str_contains($l, $clave)) {
                    return $icono;
                }
            }
        }

        return 'star';
    }

    public static function remuneracionLabel(Oferta $oferta): string
    {
        if (! $oferta->mostrar_remuneracion || ($oferta->remuneracion_min === null && $oferta->remuneracion_max === null)) {
            return 'A convenir';
        }

        $fmt = fn ($v) => 'Gs. ' . number_format((float) $v, 0, ',', '.');

        if ($oferta->remuneracion_min !== null && $oferta->remuneracion_max !== null) {
            return $fmt($oferta->remuneracion_min) . ' - ' . $fmt($oferta->remuneracion_max);
        }

        return $fmt($oferta->remuneracion_min ?? $oferta->remuneracion_max);
    }

    /** Oferta propia del empleador logueado (evita ver ofertas ajenas por URL). */
    public function ofertaPropia(int $id): ?Oferta
    {
        return Oferta::query()
            ->where('id_oferta', $id)
            ->where('id_empleador', Auth::user()->empleador->id_empleador ?? 0)
            ->first();
    }

    public function sedeDelEmpleador(): ?Sede
    {
        return Sede::query()
            ->where('id_empleador', Auth::user()->empleador->id_empleador ?? 0)
            ->orderBy('id_sede')
            ->first();
    }

    /** Traduce la fila a las claves de la tarjeta del listado. */
    public function presentacionIndex(Oferta $oferta): array
    {
        $estado = (int) $oferta->id_estado_oferta;
        $publicada = Carbon::parse($oferta->fecha_publicacion ?? now());

        $diasRestantes = null;
        if ($oferta->fecha_limite && $estado === 1) {
            $diasRestantes = max(0, (int) ceil(now()->diffInDays(Carbon::parse($oferta->fecha_limite), false)));
        }

        return [
            'id' => $oferta->id_oferta,
            'title' => $oferta->titulo,
            'logo' => static::initials($oferta->titulo),
            'status' => static::estadoSlug($estado),
            'statusLabel' => static::estadoLabel($estado),
            'publishedAt' => static::estadoSlug($estado) === 'closed'
                ? 'Cerrada el ' . Carbon::parse($oferta->fecha_cierre ?? $oferta->fecha_publicacion)->locale('es')->translatedFormat('j \d\e F \d\e Y')
                : 'Publicada el ' . $publicada->locale('es')->translatedFormat('j \d\e F \d\e Y'),
            'publishedTs' => $publicada->format('Y-m-d H:i'),
            'location' => $oferta->ubicacion?->ciudad ?? '—',
            'modality' => $oferta->modalidad?->nombre ?? '—',
            'modalityIcon' => static::iconoModalidad($oferta->modalidad?->nombre),
            'contractType' => $oferta->tipoContrato?->nombre ?? '—',
            'applicants' => $oferta->postulaciones_count ?? $oferta->postulaciones()->count(),
            'views' => (int) ($oferta->visualizaciones ?? 0),
            'daysLeft' => $diasRestantes,
        ];
    }

    // ============================================
    // Validación y persistencia del formulario
    // ============================================

    private function validar(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:100'],
            'area' => ['required', 'integer', 'exists:area_laboral,id_area'],
            'level' => ['nullable', 'string', 'max:20'],
            'description' => ['required', 'string', 'min:50', 'max:5000'],
            'modality' => ['required', 'in:presencial,remoto,hibrido'],
            'contract' => ['required', 'in:tiempo-completo,medio-tiempo,pasantia,contrato,freelance'],
            'schedule' => ['nullable', 'in:completa,parcial,flexible'],
            'deadline' => ['required', 'date', 'after:today'],
            'salary_min' => ['nullable', 'string', 'max:20'],
            'salary_max' => ['nullable', 'string', 'max:20'],
            'experience' => ['required', 'string', 'max:1000'],
            'education' => ['nullable', 'string', 'max:500'],
            'skills' => ['required', 'string', 'max:2000'],
            'soft_skills' => ['nullable', 'string', 'max:1000'],
            'benefits' => ['nullable', 'array'],
            'benefits.*' => ['string', 'in:seguro,capacitacion,horario,almuerzo,transporte,bonos,homeoffice'],
        ], [
            'title.required' => 'El título del puesto es obligatorio.',
            'title.max' => 'El título no puede superar :max caracteres.',
            'area.required' => 'Elegí el área laboral.',
            'description.required' => 'La descripción del puesto es obligatoria.',
            'description.min' => 'Describí el puesto con al menos :min caracteres.',
            'modality.required' => 'Elegí la modalidad de trabajo.',
            'contract.required' => 'Elegí el tipo de contrato.',
            'deadline.required' => 'La fecha límite es obligatoria.',
            'deadline.after' => 'La fecha límite debe ser posterior a hoy.',
            'experience.required' => 'La experiencia requerida es obligatoria.',
            'skills.required' => 'Las habilidades técnicas son obligatorias.',
        ]);

        // Slugs → etiquetas legibles.
        $validated['nivelLabel'] = match ($validated['level'] ?? '') {
            'junior' => 'Junior', 'semi-senior' => 'Semi-Senior', 'senior' => 'Senior', 'lead' => 'Lead / Tech Lead',
            default => null,
        };
        $validated['jornadaLabel'] = match ($validated['schedule'] ?? '') {
            'completa' => 'Jornada completa', 'parcial' => 'Jornada parcial', 'flexible' => 'Horario flexible',
            default => null,
        };
        $etiquetasBeneficio = [
            'seguro' => 'Seguro médico', 'capacitacion' => 'Capacitaciones', 'horario' => 'Horario flexible',
            'almuerzo' => 'Almuerzo', 'transporte' => 'Transporte', 'bonos' => 'Bonos por objetivos',
            'homeoffice' => 'Home office',
        ];
        $validated['beneficiosLabel'] = array_map(fn ($b) => $etiquetasBeneficio[$b], $validated['benefits'] ?? []);

        $parsed = fn ($texto) => $texto !== null && $texto !== ''
            ? (float) preg_replace('/[^0-9]/', '', $texto) : null;
        $validated['remMin'] = $parsed($validated['salary_min'] ?? null);
        $validated['remMax'] = $parsed($validated['salary_max'] ?? null);
        if ($validated['remMin'] === 0.0) $validated['remMin'] = null;
        if ($validated['remMax'] === 0.0) $validated['remMax'] = null;

        return $validated;
    }

    private static function textoRequisitos(array $v): string
    {
        $lineas = [];
        if (! empty($v['nivelLabel'])) $lineas[] = 'Nivel: ' . $v['nivelLabel'];
        if (! empty($v['jornadaLabel'])) $lineas[] = 'Jornada: ' . $v['jornadaLabel'];
        $lineas[] = 'Experiencia: ' . $v['experience'];
        if (! empty($v['education'])) $lineas[] = 'Formación: ' . $v['education'];
        $lineas[] = 'Habilidades: ' . $v['skills'];

        return implode("\n", $lineas);
    }

    private static function guardarRequisitos(int $idOferta, array $v): void
    {
        $skills = array_values(array_filter(array_map('trim', explode(',', $v['skills'] ?? ''))));
        foreach ($skills as $skill) {
            DB::table('requisito_oferta')->insert([
                'id_oferta' => $idOferta, 'id_tipo_req' => 1,
                'descripcion' => mb_substr($skill, 0, 255), 'obligatorio' => true,
            ]);
        }

        $soft = array_values(array_filter(array_map('trim', explode(',', $v['soft_skills'] ?? ''))));
        foreach ($soft as $s) {
            DB::table('requisito_oferta')->insert([
                'id_oferta' => $idOferta, 'id_tipo_req' => 4,
                'descripcion' => mb_substr($s, 0, 255), 'obligatorio' => false,
            ]);
        }

        DB::table('requisito_oferta')->insert([
            ['id_oferta' => $idOferta, 'id_tipo_req' => 3, 'descripcion' => mb_substr($v['experience'], 0, 255), 'obligatorio' => true],
        ]);
        if (! empty($v['education'])) {
            DB::table('requisito_oferta')->insert([
                ['id_oferta' => $idOferta, 'id_tipo_req' => 2, 'descripcion' => mb_substr($v['education'], 0, 255), 'obligatorio' => false],
            ]);
        }
    }

    private static function guardarBeneficios(int $idOferta, array $etiquetas): void
    {
        foreach ($etiquetas as $etiqueta) {
            DB::table('beneficio_oferta')->insert([
                'id_oferta' => $idOferta, 'descripcion' => $etiqueta,
            ]);
        }
    }

    /** "Capacitaciones" → slug del checkbox del formulario. */
    private function slugBeneficio(string $label): string
    {
        return match (true) {
            str_contains(mb_strtolower($label), 'seguro') => 'seguro',
            str_contains(mb_strtolower($label), 'capacitac') => 'capacitacion',
            str_contains(mb_strtolower($label), 'horario') => 'horario',
            str_contains(mb_strtolower($label), 'almuerzo') => 'almuerzo',
            str_contains(mb_strtolower($label), 'transporte') => 'transporte',
            str_contains(mb_strtolower($label), 'bono') => 'bonos',
            str_contains(mb_strtolower($label), 'home office') || str_contains(mb_strtolower($label), 'remoto') => 'homeoffice',
            default => '',
        };
    }
}
