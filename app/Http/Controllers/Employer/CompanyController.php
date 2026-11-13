<?php

namespace App\Http\Controllers\Employer;

use App\Http\Controllers\Controller;
use App\Models\Empleador;
use App\Models\Entrevista;
use App\Models\Oferta;
use App\Models\Postulacion;
use App\Models\Sede;
use App\Models\Ubicacion;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CompanyController extends Controller
{
    /**
     * Perfil "Mi empresa" con datos REALES de la BD
     * (empleador + sede/ubicación + estadísticas calculadas).
     */
    public function profile(): View
    {
        $empleador = Auth::user()->empleador;
        $usuario = Auth::user();

        // Sede principal (la primera cargada, con su ubicación geolocalizada).
        $sede = Sede::query()
            ->with('ubicacion')
            ->where('id_empleador', $empleador->id_empleador)
            ->orderBy('id_sede')
            ->first();

        // Estadísticas REALES calculadas desde la BD.
        $ofertasQuery = Oferta::query()->where('id_empleador', $empleador->id_empleador);
        $ofertasTotales = (clone $ofertasQuery)->count();
        $ofertasActivas = (clone $ofertasQuery)->where('id_estado_oferta', 1)->count();
        $postulaciones = Postulacion::query()
            ->whereIn('id_oferta', (clone $ofertasQuery)->select('id_oferta'))
            ->count();
        $entrevistas = Entrevista::query()
            ->whereIn('id_postulacion', Postulacion::query()
                ->whereIn('id_oferta', (clone $ofertasQuery)->select('id_oferta'))
                ->select('id_postulacion'))
            ->where('estado', 'programada')
            ->count();

        $stats = [
            ['icon' => 'briefcase-fill', 'modifier' => 'success', 'value' => $ofertasActivas, 'label' => 'Ofertas activas'],
            ['icon' => 'inbox-fill', 'modifier' => '', 'value' => $postulaciones, 'label' => 'Postulaciones'],
            ['icon' => 'calendar-check-fill', 'modifier' => 'purple', 'value' => $entrevistas, 'label' => 'Entrevistas programadas'],
            ['icon' => 'folder-fill', 'modifier' => 'warning', 'value' => $ofertasTotales, 'label' => 'Ofertas publicadas'],
        ];

        $progress = static::progresoEmpresa($empleador, $sede);

        // Logo real (si el archivo existe; si no, se limpia la referencia).
        $logoUrl = null;
        if ($empleador->logo) {
            if (Storage::disk('public')->exists($empleador->logo)) {
                $logoUrl = Storage::disk('public')->url($empleador->logo);
            } else {
                $empleador->logo = null;
                $empleador->save();
            }
        }

        return view('employer.company.profile', [
            'company' => [
                'name' => $empleador->nombre_empresa,
                'initials' => static::initials($empleador->nombre_empresa ?? ''),
                'ruc' => $empleador->ruc,
                'descripcion' => $empleador->descripcion,
                'sitioWeb' => $empleador->sitio_web,
                'telefono' => $empleador->telefono,
                'email' => $usuario->email,
                'registradoEn' => $usuario->fecha_registro
                    ? Carbon::parse($usuario->fecha_registro)->locale('es')->translatedFormat('F \d\e Y')
                    : null,
            ],
            'sede' => $sede,
            'logoUrl' => $logoUrl,
            'stats' => $stats,
            'progress' => $progress,
        ]);
    }

    // ============================================
    // Datos de la empresa (empleador + email de la cuenta)
    // ============================================

    public function update(Request $request): RedirectResponse
    {
        $empleador = Auth::user()->empleador;
        $usuario = Auth::user();

        $validated = $request->validate([
            'nombre_empresa' => ['required', 'string', 'max:100'],
            'ruc' => ['nullable', 'string', 'max:20', 'unique:empleador,ruc,' . $empleador->id_empleador . ',id_empleador'],
            'email' => ['required', 'email', 'max:100', 'unique:usuario,email,' . $usuario->id_usuario . ',id_usuario'],
            'sitio_web' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20'],
        ], [
            'nombre_empresa.required' => 'La razón social es obligatoria.',
            'nombre_empresa.max'      => 'La razón social no puede superar :max caracteres.',
            'ruc.unique'              => 'Ese RUC ya está registrado por otra empresa.',
            'ruc.max'                 => 'El RUC no puede superar :max caracteres.',
            'email.required'          => 'El email de contacto es obligatorio.',
            'email.email'             => 'El email de contacto no tiene un formato válido.',
            'email.unique'            => 'Ya existe una cuenta con ese correo electrónico.',
            'telefono.max'            => 'El teléfono no puede superar :max caracteres.',
        ]);

        $usuario->email = $validated['email'];
        $usuario->save();

        $empleador->nombre_empresa = $validated['nombre_empresa'];
        $empleador->ruc = $validated['ruc'];
        $empleador->sitio_web = $validated['sitio_web'];
        $empleador->telefono = $validated['telefono'];
        $empleador->save();

        return back()->with('success', 'Datos de la empresa actualizados correctamente.');
    }

    // ============================================
    // Descripción institucional
    // ============================================

    public function descripcionUpdate(Request $request): RedirectResponse
    {
        $empleador = Auth::user()->empleador;

        $validated = $request->validate([
            'descripcion' => ['required', 'string', 'max:3000'],
        ], [
            'descripcion.required' => 'La descripción no puede quedar vacía.',
            'descripcion.max'      => 'La descripción no puede superar :max caracteres.',
        ]);

        $empleador->descripcion = $validated['descripcion'];
        $empleador->save();

        return back()->with('success', 'Descripción institucional actualizada correctamente.');
    }

    // ============================================
    // Logo corporativo (Storage + columna empleador.logo)
    // ============================================

    public function logoUpdate(Request $request): RedirectResponse
    {
        $request->validate([
            'logo_file' => ['required', 'image', 'max:2048', 'mimes:jpg,jpeg,png,webp'],
        ], [
            'logo_file.required' => 'Elegí una imagen para el logo.',
            'logo_file.image'    => 'El archivo debe ser una imagen.',
            'logo_file.mimes'    => 'El logo debe ser JPG, PNG o WebP.',
            'logo_file.max'      => 'La imagen del logo no puede superar 2 MB.',
        ]);

        $empleador = Auth::user()->empleador;

        // Elimina el logo anterior si existía.
        if ($empleador->logo) {
            Storage::disk('public')->delete($empleador->logo);
        }

        $path = $request->file('logo_file')->store('logos/empleador-' . $empleador->id_empleador, 'public');
        $empleador->logo = $path;
        $empleador->save();

        return back()->with('success', 'Logo actualizado correctamente.');
    }

    public function logoDestroy(): RedirectResponse
    {
        $empleador = Auth::user()->empleador;

        if ($empleador->logo) {
            Storage::disk('public')->delete($empleador->logo);
            $empleador->logo = null;
            $empleador->save();
        }

        return back()->with('success', 'Logo eliminado correctamente.');
    }

    // ============================================
    // Ubicación de la sede principal (sede + ubicacion)
    // ============================================

    public function ubicacionUpdate(Request $request): RedirectResponse
    {
        $empleador = Auth::user()->empleador;

        $validated = $request->validate([
            'sede_nombre' => ['nullable', 'string', 'max:100'],
            'sede_telefono' => ['nullable', 'string', 'max:20'],
            'sede_horario' => ['nullable', 'string', 'max:100'],
            'direccion' => ['required', 'string', 'max:200'],
            'ciudad' => ['required', 'string', 'max:100'],
            'provincia' => ['nullable', 'string', 'max:100'],
            'pais' => ['nullable', 'string', 'max:100'],
            'latitud' => ['required', 'numeric', 'between:-90,90'],
            'longitud' => ['required', 'numeric', 'between:-180,180'],
            'referencia' => ['nullable', 'string', 'max:255'],
        ], [
            'sede_nombre.max'     => 'El nombre de la sede no puede superar :max caracteres.',
            'sede_telefono.max'   => 'El teléfono de la sede no puede superar :max caracteres.',
            'sede_horario.max'    => 'El horario no puede superar :max caracteres.',
            'direccion.required'  => 'La dirección es obligatoria.',
            'direccion.max'       => 'La dirección no puede superar :max caracteres.',
            'ciudad.required'     => 'La ciudad es obligatoria.',
            'ciudad.max'          => 'La ciudad no puede superar :max caracteres.',
            'latitud.required'    => 'La latitud es obligatoria (podés copiarla del mapa).',
            'latitud.between'     => 'La latitud no es una coordenada válida.',
            'longitud.required'   => 'La longitud es obligatoria (podés copiarla del mapa).',
            'longitud.between'    => 'La longitud no es una coordenada válida.',
            'referencia.max'      => 'La referencia no puede superar :max caracteres.',
        ]);

        $sede = Sede::query()
            ->where('id_empleador', $empleador->id_empleador)
            ->orderBy('id_sede')
            ->first();

        $datosUbicacion = [
            'direccion' => $validated['direccion'],
            'ciudad' => $validated['ciudad'],
            'provincia' => $validated['provincia'],
            'pais' => $validated['pais'] ?: 'Paraguay',
            'latitud' => $validated['latitud'],
            'longitud' => $validated['longitud'],
            'referencia' => $validated['referencia'],
        ];

        if ($sede) {
            Ubicacion::query()->where('id_ubicacion', $sede->id_ubicacion)->update($datosUbicacion);
            $sede->nombre = $validated['sede_nombre'];
            $sede->telefono = $validated['sede_telefono'];
            $sede->horario = $validated['sede_horario'];
            $sede->save();
        } else {
            $ubicacion = Ubicacion::query()->create($datosUbicacion);
            Sede::query()->create([
                'id_empleador' => $empleador->id_empleador,
                'id_ubicacion' => $ubicacion->id_ubicacion,
                'nombre' => $validated['sede_nombre'],
                'telefono' => $validated['sede_telefono'],
                'horario' => $validated['sede_horario'],
            ]);
        }

        return back()->with('success', 'Ubicación de la sede actualizada correctamente.');
    }

    // ============================================
    // Helpers
    // ============================================

    public static function initials(string $nombre): string
    {
        $partes = preg_split('/\s+/', trim($nombre)) ?: [];
        $iniciales = '';
        foreach (array_slice($partes, 0, 2) as $parte) {
            $iniciales .= mb_strtoupper(mb_substr($parte, 0, 1));
        }

        return $iniciales !== '' ? $iniciales : 'E';
    }

    /**
     * Completitud del perfil de empresa (misma fórmula en dashboard y perfil).
     * Se recalcula con datos reales de la BD.
     */
    public static function progresoEmpresa(?Empleador $empleador, ?Sede $sede = null): array
    {
        if (! $empleador) {
            return ['percentage' => 0, 'checklist' => [], 'missing' => []];
        }

        $sede = $sede ?: $empleador->sedes()->with('ubicacion')->orderBy('id_sede')->first();

        $items = [
            ['label' => 'Datos de la empresa', 'done' => (bool) $empleador->nombre_empresa],
            ['label' => 'RUC', 'done' => (bool) $empleador->ruc],
            ['label' => 'Descripción institucional', 'done' => (bool) $empleador->descripcion],
            ['label' => 'Logo corporativo', 'done' => (bool) $empleador->logo],
            ['label' => 'Teléfono de contacto', 'done' => (bool) $empleador->telefono],
            ['label' => 'Ubicación de la sede', 'done' => (bool) $sede?->ubicacion],
        ];

        $done = count(array_filter($items, fn ($item) => $item['done']));
        $missing = array_values(array_map(fn ($item) => $item['label'], array_filter($items, fn ($item) => ! $item['done'])));

        return [
            'percentage' => (int) round($done / count($items) * 100),
            'checklist' => $items,
            'missing' => $missing,
        ];
    }
}
