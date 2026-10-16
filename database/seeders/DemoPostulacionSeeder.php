<?php

namespace Database\Seeders;

use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * DATOS DEMO del ciclo completo del candidato:
 * postulaciones (+ historial de estados), entrevistas y
 * notificaciones — coherentes con las 7 ofertas del
 * DemoOfertaSeeder (ids 1-7).
 *
 * Idempotente y NO altera la estructura de la BD.
 */
class DemoPostulacionSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================
        // 1) CATÁLOGOS
        // ============================================
        DB::table('estado_postulacion')->insertOrIgnore([
            ['id_estado_post' => 1, 'nombre' => 'Registrada'],
            ['id_estado_post' => 2, 'nombre' => 'En revisión'],
            ['id_estado_post' => 3, 'nombre' => 'Aceptada'],
            ['id_estado_post' => 4, 'nombre' => 'Rechazada'],
            ['id_estado_post' => 5, 'nombre' => 'Retirada'],
            ['id_estado_post' => 6, 'nombre' => 'Entrevista'],
        ]);

        DB::table('tipo_notificacion')->insertOrIgnore([
            ['id_tipo_notif' => 1, 'nombre' => 'Postulación'],
            ['id_tipo_notif' => 2, 'nombre' => 'Entrevista'],
            ['id_tipo_notif' => 3, 'nombre' => 'Oferta'],
        ]);

        // Catálogos del perfil: niveles de idioma e idiomas.
        DB::table('nivel_idioma')->insertOrIgnore([
            ['id_nivel' => 1, 'nombre' => 'Básico', 'orden' => 1],
            ['id_nivel' => 2, 'nombre' => 'Intermedio', 'orden' => 2],
            ['id_nivel' => 3, 'nombre' => 'Avanzado', 'orden' => 3],
            ['id_nivel' => 4, 'nombre' => 'Nativo', 'orden' => 4],
        ]);

        DB::table('idioma')->insertOrIgnore([
            ['id_idioma' => 1, 'nombre' => 'Español', 'codigo_iso' => 'spa'],
            ['id_idioma' => 2, 'nombre' => 'Inglés', 'codigo_iso' => 'eng'],
            ['id_idioma' => 3, 'nombre' => 'Portugués', 'codigo_iso' => 'por'],
            ['id_idioma' => 4, 'nombre' => 'Guaraní', 'codigo_iso' => 'gn'],
            ['id_idioma' => 5, 'nombre' => 'Francés', 'codigo_iso' => 'fre'],
            ['id_idioma' => 6, 'nombre' => 'Alemán', 'codigo_iso' => 'ger'],
            ['id_idioma' => 7, 'nombre' => 'Italiano', 'codigo_iso' => 'ita'],
        ]);

        // Habilidades demo para el candidato de pruebas (Juan, candidato #2):
        // le dan señales al recomendador de ofertas del dashboard.
        // (Matias #4 queda con su perfil real, sin tocar.)
        if (DB::table('candidato')->where('id_candidato', 2)->where('id_usuario', 4)->exists()) {
            $aptitudesDemo = [
                ['PHP', 'Intermedio'],
                ['SQL', 'Avanzado'],
                ['JavaScript', 'Intermedio'],
                ['Git', 'Básico'],
                ['Inglés', null], // null = en realidad es un idioma, no una aptitud
            ];
            foreach ($aptitudesDemo as [$nombre, $nivel]) {
                if ($nivel === null) {
                    // el idioma se linkea via candidato_idioma
                    $idIdioma = DB::table('idioma')->whereRaw('LOWER(nombre) = LOWER(?)', [$nombre])->value('id_idioma');
                    if ($idIdioma) {
                        DB::table('candidato_idioma')->insertOrIgnore([
                            ['id_candidato' => 2, 'id_idioma' => $idIdioma, 'id_nivel' => 2],
                        ]);
                    }
                    continue;
                }

                $idAptitud = DB::table('aptitud')->whereRaw('LOWER(nombre) = LOWER(?)', [$nombre])->value('id_aptitud');
                if (! $idAptitud) {
                    $idAptitud = DB::table('aptitud')->insertGetId([
                        'nombre' => $nombre,
                        'tipo' => 'Habilidad',
                        'activo' => true,
                    ], 'id_aptitud');
                }
                DB::table('candidato_aptitud')->insertOrIgnore([
                    ['id_candidato' => 2, 'id_aptitud' => $idAptitud, 'nivel' => $nivel],
                ]);
            }
        }

        // ============================================
        // 2) POSTULACIONES DEMO
        //    Matias Aguero = candidato #4 (cuenta del usuario)
        //    Juan Pérez (candidato #2) también recibe algunas
        //    para que su panel no esté vacío.
        //    key: id_oferta => [idEstado, fecha, entrevista?, retirada]
        // ============================================
        $demo = [
            4 => [
                [
                    'id_oferta' => 2, 'id_estado_post' => 3, 'fecha' => '-8 days', // Aceptada
                    'entrevista' => true, 'entrevistaPasada' => true, 'entrevistaProxima' => true,
                ],
                [
                    'id_oferta' => 5, 'id_estado_post' => 2, 'fecha' => '-5 days', // En revisión
                ],
                [
                    'id_oferta' => 1, 'id_estado_post' => 1, 'fecha' => '-2 hours', // Registrada
                ],
                [
                    'id_oferta' => 3, 'id_estado_post' => 4, 'fecha' => '-12 days', // Rechazada
                    'entrevista' => true, 'entrevistaPasada' => true,
                ],
                [
                    'id_oferta' => 6, 'id_estado_post' => 5, 'fecha' => '-6 days', // Retirada por el candidato
                    'retirada' => '-5 days',
                ],
            ],
            2 => [
                [
                    'id_oferta' => 2, 'id_estado_post' => 3, 'fecha' => '-8 days', // Aceptada
                    'entrevista' => true, 'entrevistaPasada' => true, 'entrevistaProxima' => true,
                ],
                [
                    'id_oferta' => 5, 'id_estado_post' => 2, 'fecha' => '-5 days', // En revisión
                ],
                [
                    'id_oferta' => 1, 'id_estado_post' => 1, 'fecha' => '-2 hours', // Registrada
                ],
                [
                    'id_oferta' => 3, 'id_estado_post' => 4, 'fecha' => '-12 days', // Rechazada
                    'entrevista' => true, 'entrevistaPasada' => true,
                ],
                [
                    'id_oferta' => 6, 'id_estado_post' => 5, 'fecha' => '-6 days', // Retirada
                    'retirada' => '-5 days',
                ],
            ],
        ];

        $fechaFormat = fn ($d) => now()->add($d);

        foreach ($demo as $idCandidato => $postulaciones) {
            foreach ($postulaciones as $p) {
                $existe = DB::table('postulacion')
                    ->where('id_candidato', $idCandidato)
                    ->where('id_oferta', $p['id_oferta'])
                    ->exists();
                if ($existe) {
                    continue;
                }

                $idPostulacion = DB::table('postulacion')->insertGetId([
                    'id_candidato' => $idCandidato,
                    'id_oferta' => $p['id_oferta'],
                    'id_estado_post' => $p['id_estado_post'],
                    'fecha_postulacion' => $fechaFormat($p['fecha']),
                ], 'id_postulacion');

                // Historial: Registrada -> ... -> estado actual
                $camino = match ($p['id_estado_post']) {
                    1 => [1],
                    2 => [1, 2],
                    3 => [1, 2, 6, 3], // incluye paso por Entrevista
                    4 => [1, 2, 4],
                    5 => [1, 5],
                    default => [1],
                };
                foreach ($camino as $i => $idEstado) {
                    DB::table('historial_estado')->insert([
                        'id_postulacion' => $idPostulacion,
                        'id_estado_post' => $idEstado,
                        'fecha_cambio' => $fechaFormat($p['fecha'])->addHours($i),
                        'comentario' => null,
                    ]);
                }

                if (isset($p['retirada'])) {
                    DB::table('historial_estado')->insert([
                        'id_postulacion' => $idPostulacion,
                        'id_estado_post' => 5,
                        'fecha_cambio' => $fechaFormat($p['retirada']),
                        'comentario' => 'Retirada por el candidato.',
                    ]);
                }

                // ============================================
                // 3) ENTREVISTAS DEMO (si corresponde)
                // ============================================
                if (! empty($p['entrevista'])) {
                    $fechaEntrevista = ! empty($p['entrevistaPasada'])
                        ? now()->add('-9 days')->setTime(10, 0)
                        : now()->addDays(5)->setTime(10, 0);

                    $estadoEntrevista = ! empty($p['entrevistaPasada']) ? 'realizada' : 'pendiente';

                    DB::table('entrevista')->insert([
                        'id_postulacion' => $idPostulacion,
                        'fecha_hora' => $fechaEntrevista,
                        'duracion_minutos' => 90,
                        'modalidad' => ! empty($p['entrevistaPasada']) ? 'Presencial' : 'Videollamada',
                        'enlace_virtual' => ! empty($p['entrevistaPasada']) ? null : 'https://meet.google.com/demo-worknet',
                        'notas' => 'Preparar ejemplos de proyectos anteriores. El equipo técnico evaluará tu experiencia con herramientas del puesto.',
                        'estado' => $estadoEntrevista,
                        'fecha_creacion' => now()->add('-6 days'),
                    ]);
                }

                // Entrevista PRÓXIMA adicional (demo de cancelar / negociar).
                if (! empty($p['entrevistaProxima'])) {
                    $existeProxima = DB::table('entrevista')
                        ->where('id_postulacion', $idPostulacion)
                        ->whereIn('estado', ['pendiente', 'negociando'])
                        ->exists();
                    if (! $existeProxima) {
                        DB::table('entrevista')->insert([
                            'id_postulacion' => $idPostulacion,
                            'fecha_hora' => now()->addDays(5)->setTime(10, 0),
                            'duracion_minutos' => 90,
                            'modalidad' => 'Videollamada',
                            'enlace_virtual' => 'https://meet.google.com/demo-worknet',
                            'notas' => 'Hola, te confirmo la entrevista técnica para la fecha acordada. Por el enlace de la videollamada te conectás directo con el equipo.',
                            'estado' => 'pendiente',
                            'fecha_creacion' => now()->add('-6 days'),
                        ]);
                    }
                }
            }
        }

        // ============================================
        // 3b) Asegurar entrevista PRÓXIMA demo (cancelar/negociar)
        //     para postulaciones que ya existían (idempotencia).
        // ============================================
        foreach ($demo as $idCandidato => $postulaciones) {
            foreach ($postulaciones as $p) {
                if (empty($p['entrevistaProxima'])) {
                    continue;
                }

                $idPostulacion = DB::table('postulacion')
                    ->where('id_candidato', $idCandidato)
                    ->where('id_oferta', $p['id_oferta'])
                    ->value('id_postulacion');
                if (! $idPostulacion) {
                    continue;
                }

                $existeProxima = DB::table('entrevista')
                    ->where('id_postulacion', $idPostulacion)
                    ->whereIn('estado', ['pendiente', 'negociando'])
                    ->exists();
                if ($existeProxima) {
                    continue;
                }

                DB::table('entrevista')->insert([
                    'id_postulacion' => $idPostulacion,
                    'fecha_hora' => now()->addDays(5)->setTime(10, 0),
                    'duracion_minutos' => 90,
                    'modalidad' => 'Videollamada',
                    'enlace_virtual' => 'https://meet.google.com/demo-worknet',
                    'notas' => 'Hola, te confirmo la entrevista técnica para la fecha acordada. Por el enlace de la videollamada te conectás directo con el equipo.',
                    'estado' => 'pendiente',
                    'fecha_creacion' => now()->add('-6 days'),
                ]);
            }
        }

        // ============================================
        // 4) NOTIFICACIONES DEMO para Matias Aguero (usr #8)
        //    espejo del prototipo (hoy / ayer / anteriores).
        // ============================================
        $tipos = [
            ['titulo' => 'Nueva entrevista programada', 'mensaje' => 'DataCorp S.A. te convocó a una entrevista técnica. Podés ver los detalles en "Mis entrevistas".', 'tipo' => 2, 'url' => '/entrevistas', 'ref' => ['entrevista', null]],
            ['titulo' => '¡Buenas noticias!', 'mensaje' => 'Tu postulación a Analista de Sistemas en DataCorp S.A. fue Aceptada. El equipo de RRHH se pondrá en contacto pronto.', 'tipo' => 1, 'url' => '/candidato/postulaciones', 'ref' => ['postulacion', null]],
            ['titulo' => 'Postulación enviada', 'mensaje' => 'Te postulaste exitosamente a Desarrollador Full Stack Laravel en Concesionaria San Miguel.', 'tipo' => 1, 'url' => '/candidato/postulaciones', 'ref' => ['postulacion', null]],
            ['titulo' => 'Tu postulación está en revisión', 'mensaje' => 'Banco Regional está revisando tu postulación para Administrador de Base de Datos.', 'tipo' => 1, 'url' => '/candidato/postulaciones', 'ref' => ['postulacion', null]],
            ['titulo' => 'Nuevas ofertas para vos', 'mensaje' => 'Encontramos ofertas nuevas que coinciden con tu perfil. Mirá el mapa para verlas todas.', 'tipo' => 3, 'url' => '/mapa', 'ref' => ['oferta', null]],
        ];

        $leidas = [3 => true]; // la 4ª ya está leída (como el prototipo)
        $fechas = ['15 minutes ago', '3 hours ago', '-6 hours', '-1 day', '-2 days'];

        foreach (array_keys($demo) as $idCandidato) {
            $usuario = DB::table('candidato')->where('id_candidato', $idCandidato)->value('id_usuario');
            if (! $usuario || DB::table('postulacion')->where('id_candidato', $idCandidato)->count() === 0) {
                continue;
            }

            foreach ($tipos as $i => $n) {
                $existe = DB::table('notificacion')
                    ->where('id_usuario', $usuario)
                    ->where('titulo', $n['titulo'])
                    ->exists();
                if ($existe) {
                    continue;
                }

                DB::table('notificacion')->insert([
                    'id_usuario' => $usuario,
                    'id_tipo_notif' => $n['tipo'],
                    'titulo' => $n['titulo'],
                    'mensaje' => $n['mensaje'],
                    'url_destino' => $n['url'],
                    'fecha' => now()->add($fechas[$i]),
                    'leida' => $leidas[$i] ?? false,
                ]);
            }
        }

        $this->command?->info('DemoPostulacionSeeder: postulaciones, historial, entrevistas y notificaciones demo listos.');
    }
}
