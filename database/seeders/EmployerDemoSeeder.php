<?php

namespace Database\Seeders;

use App\Models\Empleador;
use App\Models\Oferta;
use App\Models\Sede;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * DATOS DEMO del módulo EMPLEADOR para Tech Solutions
 * (cuenta TechSolutions@gmail.com del usuario; fallback: empresa@test.com).
 *
 * Contiene las ofertas del prototipo, los postulantes demo
 * (usuarios + candidatos reales con perfil completo), sus
 * postulaciones (+ historial), las entrevistas y las
 * notificaciones del empleador.
 *
 * Idempotente y NO altera la estructura de la BD: solo inserta filas.
 */
class EmployerDemoSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================
        // 0) SINCRONIZAR sequences de Postgres
        //    (los seeders demo insertaron filas con id explícito,
        //     así que las sequences de serial quedaron atrasadas).
        // ============================================
        $tablasSerial = [
            'oferta' => 'id_oferta',
            'usuario' => 'id_usuario',
            'candidato' => 'id_candidato',
            'ubicacion' => 'id_ubicacion',
            'sede' => 'id_sede',
            'postulacion' => 'id_postulacion',
            'historial_estado' => 'id_historial',
            'entrevista' => 'id_entrevista',
            'notificacion' => 'id_notificacion',
            'experiencia' => 'id_experiencia',
            'titulo' => 'id_titulo',
            'aptitud' => 'id_aptitud',
            'requisito_oferta' => 'id_requisito',
            'beneficio_oferta' => 'id_beneficio',
        ];

        foreach ($tablasSerial as $tabla => $columna) {
            $seq = DB::selectOne('SELECT pg_get_serial_sequence(?, ?) AS seq', [$tabla, $columna]);
            if ($seq?->seq) {
                DB::statement(
                    "SELECT setval(?, GREATEST((SELECT COALESCE(MAX($columna), 1) FROM $tabla), 1), true)",
                    [$seq->seq]
                );
            }
        }

        // ============================================
        // 0bis) CATÁLOGOS que faltaban (solo filas)
        // ============================================
        DB::table('tipo_requisito')->insertOrIgnore([
            ['id_tipo_req' => 1, 'nombre' => 'Habilidad técnica'],
            ['id_tipo_req' => 2, 'nombre' => 'Formación académica'],
            ['id_tipo_req' => 3, 'nombre' => 'Experiencia'],
            ['id_tipo_req' => 4, 'nombre' => 'Otro'],
        ]);

        // ============================================
        // 1) EMPRESA: Tech Solutions S.A. + su sede
        // ============================================
        // La cuenta demo prioriza el usuario real "TechSolutions"
        // (TechSolutions@gmail.com); si no existe, usa la de prueba.
        $usuarioEmpresa = Usuario::query()->whereIn('email', ['TechSolutions@gmail.com', 'empresa@test.com'])
            ->orderByRaw("case when email = 'TechSolutions@gmail.com' then 0 else 1 end")
            ->first();
        if (! $usuarioEmpresa) {
            $this->command?->warn('EmployerDemoSeeder: no existe ninguna cuenta Tech Solutions (corré UsuarioSeeder primero).');

            return;
        }

        $empleador = Empleador::query()->where('id_usuario', $usuarioEmpresa->id_usuario)->first();
        if (! $empleador) {
            $this->command?->warn('EmployerDemoSeeder: no hay fila empleador para ' . $usuarioEmpresa->email . '.');

            return;
        }

        // La sede la crea UsuarioSeeder; si no existe, la creamos acá (mismo criterio).
        $sede = Sede::query()->where('id_empleador', $empleador->id_empleador)->orderBy('id_sede')->first();
        if (! $sede) {
            $idUbicacion = DB::table('ubicacion')->insertGetId([
                'direccion' => 'Av. Irrazábal 1234',
                'ciudad' => 'Encarnación',
                'provincia' => 'Itapúa',
                'pais' => 'Paraguay',
                'latitud' => -27.3306,
                'longitud' => -55.8667,
                'referencia' => 'A dos cuadras de la Costanera',
            ], 'id_ubicacion');

            $idSede = DB::table('sede')->insertGetId([
                'id_empleador' => $empleador->id_empleador,
                'id_ubicacion' => $idUbicacion,
                'nombre' => 'Casa Central',
                'telefono' => '+595 71 200 300',
                'horario' => 'Lun a Vie 08:00 - 17:00',
            ], 'id_sede');

            $sede = Sede::query()->find($idSede);
        }

        $idUbicacionSede = $sede->id_ubicacion;

        // ============================================
        // 2) OFERTAS del prototipo (bajo Tech Solutions)
        //    Sin id fijo: idempotente por título.
        // ============================================
        $ofertasDemo = [
            [
                'titulo' => 'Desarrollador Laravel Senior',
                'estado' => 1, // Activa
                'area' => 1, 'modalidad' => 3, 'contrato' => 1, // Híbrido, Tiempo completo
                'descripcion' => 'Estamos buscando un Desarrollador Laravel Senior para liderar el desarrollo de nuestras aplicaciones web internas. La persona seleccionada será responsable del diseño de arquitectura, la implementación de nuevas funcionalidades, la integración con APIs de terceros, la optimización del rendimiento y la mentoría de desarrolladores junior.',
                'experiencia' => '5+ años en desarrollo backend con PHP y Laravel. Experiencia comprobable en proyectos de mediana a gran escala.',
                'formacion' => 'Licenciatura en Análisis de Sistemas, Ingeniería Informática o similar.',
                'nivel' => 'senior', 'jornada' => 'completa',
                'skills' => ['PHP 8.3+', 'Laravel 12', 'PostgreSQL', 'Docker', 'Git', 'APIs REST', 'Testing'],
                'softSkills' => ['Trabajo en equipo', 'Comunicación efectiva', 'Proactividad'],
                'remMin' => 7000000, 'remMax' => 10000000, 'mostrarRem' => true,
                'beneficios' => [['Seguro médico'], ['Capacitaciones'], ['Horario flexible'], ['Home office']],
                'publicada' => '-3 days', 'limite' => '+15 days', 'cierre' => null,
                'vacantes' => 1, 'vistas' => 245,
            ],
            [
                'titulo' => 'Analista de Sistemas',
                'estado' => 1,
                'area' => 1, 'modalidad' => 1, 'contrato' => 1, // Presencial, Tiempo completo
                'descripcion' => 'Buscamos un Analista de Sistemas para liderar el relevamiento de requerimientos con el cliente, la documentación funcional y técnica de nuestros proyectos, y el seguimiento del desarrollo junto al equipo de programación. Es un rol clave entre el negocio y la tecnología.',
                'experiencia' => '3+ años en análisis de sistemas o análisis funcional en proyectos de software.',
                'formacion' => 'Licenciatura en Análisis de Sistemas, Ingeniería Informática o similar.',
                'nivel' => 'semi-senior', 'jornada' => 'completa',
                'skills' => ['Análisis funcional', 'UML', 'SQL', 'Metodologías ágiles', 'Documentación', 'Scrum'],
                'softSkills' => ['Comunicación con clientes', 'Organización', 'Trabajo en equipo'],
                'remMin' => 6000000, 'remMax' => 8000000, 'mostrarRem' => true,
                'beneficios' => [['Seguro médico'], ['Almuerzo'], ['Transporte'], ['Bonos por objetivos']],
                'publicada' => '-7 days', 'limite' => '+8 days', 'cierre' => null,
                'vacantes' => 1, 'vistas' => 182,
            ],
            [
                'titulo' => 'Diseñador UX/UI',
                'estado' => 2, // Pausada
                'area' => 2, 'modalidad' => 2, 'contrato' => 5, // Remoto, Freelance
                'descripcion' => 'Buscamos un Diseñador UX/UI para crear experiencias digitales memorables: investigación con usuarios, wireframes, prototipos de alta fidelidad y diseño de interfaces para nuestras plataformas web y móviles.',
                'experiencia' => '2+ años diseñando productos digitales. Portafolio con casos de UX/UI.',
                'formacion' => 'Diseño Gráfico, Diseño Digital o afines (no excluyente).',
                'nivel' => 'semi-senior', 'jornada' => 'flexible',
                'skills' => ['Figma', 'Adobe XD', 'Design Thinking', 'Prototipado', 'User Research'],
                'softSkills' => ['Creatividad', 'Atención al detalle'],
                'remMin' => null, 'remMax' => null, 'mostrarRem' => false, // A convenir
                'beneficios' => [['Trabajo 100% remoto'], ['Horario flexible'], ['Proyectos variados']],
                'publicada' => '-12 days', 'limite' => '+20 days', 'cierre' => null,
                'vacantes' => 1, 'vistas' => 98,
            ],
            [
                'titulo' => 'Desarrollador Full Stack',
                'estado' => 1,
                'area' => 1, 'modalidad' => 2, 'contrato' => 1, // Remoto, Tiempo completo
                'descripcion' => 'Buscamos un Desarrollador Full Stack para construir de punta a punta las funcionalidades de nuestras plataformas: backend en PHP con Laravel, frontend en JavaScript y bases de datos relacionales. Trabajás en un equipo chico, con autonomía y código en producción desde el primer mes.',
                'experiencia' => '2+ años en desarrollo web full stack (backend y frontend).',
                'formacion' => 'Estudiante avanzado o graduado de carreras de sistemas.',
                'nivel' => 'junior', 'jornada' => 'completa',
                'skills' => ['PHP', 'Laravel', 'JavaScript', 'MySQL', 'Git', 'REST'],
                'softSkills' => ['Autonomía', 'Curiosidad', 'Comunicación'],
                'remMin' => 7000000, 'remMax' => 9000000, 'mostrarRem' => true,
                'beneficios' => [['Trabajo 100% remoto'], ['Capacitaciones'], ['Equipos modernos']],
                'publicada' => '-1 day', 'limite' => '+20 days', 'cierre' => null,
                'vacantes' => 2, 'vistas' => 134,
            ],
            [
                'titulo' => 'Administrador de Base de Datos',
                'estado' => 3, // Cerrada
                'area' => 1, 'modalidad' => 1, 'contrato' => 1, // Presencial, Tiempo completo
                'descripcion' => 'Buscamos un Administrador de Base de Datos con experiencia en PostgreSQL para gestionar la infraestructura de datos crítica de la empresa: réplicas, backups, tuning de consultas y alta disponibilidad.',
                'experiencia' => '4+ años administrando bases de datos PostgreSQL en producción.',
                'formacion' => 'Licenciatura en Sistemas o afines.',
                'nivel' => 'senior', 'jornada' => 'completa',
                'skills' => ['PostgreSQL', 'Replicación', 'Backups', 'Tuning', 'Alta disponibilidad'],
                'softSkills' => ['Rigor', 'Trabajo bajo presión'],
                'remMin' => 8000000, 'remMax' => 10000000, 'mostrarRem' => true,
                'beneficios' => [['Seguro médico familiar'], ['Bonos anuales'], ['Estabilidad laboral']],
                'publicada' => '-35 days', 'limite' => '-20 days', 'cierre' => '-20 days',
                'vacantes' => 1, 'vistas' => 420,
            ],
        ];

        $ofertasPorTitulo = [];

        foreach ($ofertasDemo as $o) {
            $existente = Oferta::query()
                ->where('id_empleador', $empleador->id_empleador)
                ->where('titulo', $o['titulo'])
                ->first();

            if ($existente) {
                $ofertasPorTitulo[$o['titulo']] = $existente;
                continue; // idempotente: nunca sobrescribir datos del usuario
            }

            // Texto de requisitos (lo que ven los candidatos en el mapa/empresa).
            $textoRequisitos = "Nivel: {$o['nivel']}\nJornada: {$o['jornada']}\n"
                . 'Experiencia: ' . $o['experiencia'] . "\n"
                . 'Formación: ' . $o['formacion'] . "\n"
                . 'Habilidades: ' . implode(', ', $o['skills']);

            $textoBeneficios = implode(', ', array_map(fn ($b) => $b[0], $o['beneficios']));

            $idOferta = DB::table('oferta')->insertGetId([
                'id_empleador' => $empleador->id_empleador,
                'id_ubicacion' => $idUbicacionSede, // La dirección SIEMPRE es la de la sede del perfil
                'id_area' => $o['area'],
                'id_modalidad' => $o['modalidad'],
                'id_tipo_contrato' => $o['contrato'],
                'id_estado_oferta' => $o['estado'],
                'titulo' => $o['titulo'],
                'descripcion' => $o['descripcion'],
                'requisitos' => $textoRequisitos,
                'forma_postulacion' => 'Postulá por Work.net con tu CV actualizado',
                'remuneracion_min' => $o['remMin'],
                'remuneracion_max' => $o['remMax'],
                'mostrar_remuneracion' => $o['mostrarRem'],
                'beneficios' => $textoBeneficios,
                'fecha_publicacion' => now()->modify($o['publicada']),
                'fecha_limite' => now()->modify($o['limite']),
                'fecha_cierre' => $o['cierre'] ? now()->modify($o['cierre']) : null,
                'vacantes' => $o['vacantes'],
                'visualizaciones' => $o['vistas'],
            ], 'id_oferta');

            // Requisitos estructurados (chips del detalle / prefill de edición).
            foreach ($o['skills'] as $skill) {
                DB::table('requisito_oferta')->insert([
                    'id_oferta' => $idOferta, 'id_tipo_req' => 1,
                    'descripcion' => $skill, 'obligatorio' => true,
                ]);
            }
            foreach ($o['softSkills'] as $soft) {
                DB::table('requisito_oferta')->insert([
                    'id_oferta' => $idOferta, 'id_tipo_req' => 4,
                    'descripcion' => $soft, 'obligatorio' => false,
                ]);
            }
            DB::table('requisito_oferta')->insert([
                ['id_oferta' => $idOferta, 'id_tipo_req' => 3, 'descripcion' => $o['experiencia'], 'obligatorio' => true],
                ['id_oferta' => $idOferta, 'id_tipo_req' => 2, 'descripcion' => $o['formacion'], 'obligatorio' => false],
            ]);

            foreach ($o['beneficios'] as [$beneficio]) {
                DB::table('beneficio_oferta')->insert([
                    'id_oferta' => $idOferta, 'descripcion' => $beneficio,
                ]);
            }

            $ofertasPorTitulo[$o['titulo']] = Oferta::query()->find($idOferta);
        }

        // ============================================
        // 3) POSTULANTES DEMO (usuario + candidato + perfil)
        //    Emails @demo.worknet, contraseña demo1234.
        // ============================================
        $demoCandidatos = [
            [
                'nombre' => 'María González',
                'email' => 'maria.gonzalez@demo.worknet',
                'nacimiento' => '-28 years', 'genero' => 'Femenino',
                'universidad' => 'Universidad Nacional de Itapúa', 'es_estudiante' => false,
                'estado_laboral' => 'Trabajando actualmente',
                'perfil' => 'Desarrolladora backend especializada en PHP y Laravel con foco en APIs REST, bases de datos PostgreSQL y testing. Me apasiona escribir código limpio, documentado y con buen rendimiento.',
                'experiencias' => [
                    ['puesto' => 'Desarrolladora Backend Laravel', 'empresa' => 'DataCorp S.A.', 'desde' => '-3 years', 'hasta' => null,
                        'tareas' => 'Desarrollo de APIs REST con Laravel 12 y PostgreSQL, integración con servicios externos, testing con PHPUnit y mentoría de juniors.'],
                    ['puesto' => 'Desarrolladora Web PHP', 'empresa' => 'Creative Studio', 'desde' => '-5 years', 'hasta' => '-3 years',
                        'tareas' => 'Desarrollo de sitios y aplicaciones web con PHP, MySQL y JavaScript. Docker para entornos locales y Git para control de versiones.'],
                ],
                'titulos' => [
                    ['tipo' => 'Grado', 'nombre' => 'Ing. Informática', 'institucion' => 'Universidad Nacional de Itapúa', 'desde' => '-8 years', 'hasta' => '-4 years', 'estado' => 'Finalizado'],
                ],
                'aptitudes' => [['PHP', 'Avanzado'], ['SQL', 'Avanzado'], ['JavaScript', 'Intermedio'], ['Docker', 'Intermedio'], ['Testing', 'Intermedio']],
                'idiomas' => [['Español', 4], ['Inglés', 3], ['Guaraní', 2]],
            ],
            [
                'nombre' => 'Lucía Benítez',
                'email' => 'lucia.benitez@demo.worknet',
                'nacimiento' => '-32 years', 'genero' => 'Femenino',
                'universidad' => 'Universidad Autónoma de Encarnación', 'es_estudiante' => false,
                'estado_laboral' => 'Trabajando actualmente',
                'perfil' => 'Analista funcional con experiencia en relevamiento de requerimientos, documentación funcional, modelado UML y BPMN, y trabajo con metodologías ágiles Scrum. Punto de puente entre el negocio y el equipo de desarrollo.',
                'experiencias' => [
                    ['puesto' => 'Analista Funcional Senior', 'empresa' => 'Banco Regional', 'desde' => '-4 years', 'hasta' => null,
                        'tareas' => 'Relevamiento de requerimientos con clientes, documentación funcional, diagramas UML y BPMN, seguimiento del desarrollo con Scrum y Jira.'],
                    ['puesto' => 'Analista de Sistemas', 'empresa' => 'DataCorp S.A.', 'desde' => '-7 years', 'hasta' => '-4 years',
                        'tareas' => 'Análisis funcional de sistemas web, especificaciones técnicas, SQL para validación de datos y capacitación de usuarios.'],
                ],
                'titulos' => [
                    ['tipo' => 'Grado', 'nombre' => 'Ing. Informática', 'institucion' => 'Universidad Autónoma de Encarnación', 'desde' => '-12 years', 'hasta' => '-8 years', 'estado' => 'Finalizado'],
                ],
                'aptitudes' => [['Análisis funcional', 'Avanzado'], ['UML', 'Avanzado'], ['SQL', 'Intermedio'], ['Scrum', 'Avanzado'], ['Documentación', 'Intermedio']],
                'idiomas' => [['Español', 4], ['Inglés', 2]],
            ],
            [
                'nombre' => 'Carlos Ramírez',
                'email' => 'carlos.ramirez@demo.worknet',
                'nacimiento' => '-27 years', 'genero' => 'Masculino',
                'universidad' => 'Universidad del Este', 'es_estudiante' => false,
                'estado_laboral' => 'Buscando empleo',
                'perfil' => 'Analista de sistemas junior con interés en la documentación de procesos y el modelado de datos. Conocimientos de SQL y metodologías ágiles.',
                'experiencias' => [
                    ['puesto' => 'Analista de Sistemas Jr', 'empresa' => 'App Factory', 'desde' => '-3 years', 'hasta' => null,
                        'tareas' => 'Soporte al relevamiento de requerimientos, diagramas UML básicos, consultas SQL para reportes y pruebas de sistemas.'],
                ],
                'titulos' => [
                    ['tipo' => 'Grado', 'nombre' => 'Lic. en Sistemas', 'institucion' => 'Universidad del Este', 'desde' => '-9 years', 'hasta' => '-5 years', 'estado' => 'Finalizado'],
                ],
                'aptitudes' => [['Análisis funcional', 'Intermedio'], ['UML', 'Intermedio'], ['SQL', 'Intermedio'], ['Scrum', 'Básico']],
                'idiomas' => [['Español', 4], ['Inglés', 2]],
            ],
            [
                'nombre' => 'Pedro Almirón',
                'email' => 'pedro.almiron@demo.worknet',
                'nacimiento' => '-24 years', 'genero' => 'Masculino',
                'universidad' => 'Universidad Nacional de Asunción', 'es_estudiante' => true,
                'estado_laboral' => 'Estudiando y trabajando',
                'perfil' => 'Estudiante avanzado de informática, dando mis primeros pasos en el desarrollo web con PHP.',
                'experiencias' => [
                    ['puesto' => 'Desarrollador Web (part-time)', 'empresa' => 'Tech Help Paraguay', 'desde' => '-2 years', 'hasta' => null,
                        'tareas' => 'Mantenimiento de sitios web en PHP con CodeIgniter y soporte a usuarios.'],
                ],
                'titulos' => [
                    ['tipo' => 'Grado', 'nombre' => 'Ing. Informática (en curso)', 'institucion' => 'Universidad Nacional de Asunción', 'desde' => '-4 years', 'hasta' => null, 'estado' => 'En curso'],
                ],
                'aptitudes' => [['PHP', 'Básico'], ['CodeIgniter', 'Básico'], ['MySQL', 'Básico']],
                'idiomas' => [['Español', 4], ['Portugués', 2]],
            ],
        ];

        $candidatosPorNombre = [];

        foreach ($demoCandidatos as $c) {
            $usuario = Usuario::query()->where('email', $c['email'])->first();

            if (! $usuario) {
                $idUsuario = DB::table('usuario')->insertGetId([
                    'nombre' => $c['nombre'],
                    'email' => $c['email'],
                    'password' => Hash::make('demo1234'),
                    'rol' => 'candidato',
                    'estado' => 'activo',
                    'fecha_registro' => now()->modify('-40 days'),
                ], 'id_usuario');
            } else {
                $idUsuario = $usuario->id_usuario;
            }

            $idCandidato = DB::table('candidato')->where('id_usuario', $idUsuario)->value('id_candidato');

            if (! $idCandidato) {
                $idCandidato = DB::table('candidato')->insertGetId([
                    'id_usuario' => $idUsuario,
                    'fecha_nacimiento' => now()->modify($c['nacimiento'])->format('Y-m-d'),
                    'genero' => $c['genero'],
                    'nacionalidad' => 'Paraguaya',
                    'es_estudiante' => $c['es_estudiante'],
                    'universidad' => $c['universidad'],
                    'estado_laboral' => $c['estado_laboral'],
                    'perfil' => $c['perfil'],
                ], 'id_candidato');
            }

            // CV de demo para María (se copia el PDF de ejemplo que ya
            // existe en storage; no se toca la estructura de la BD).
            if ($c['email'] === 'maria.gonzalez@demo.worknet') {
                $cvActual = DB::table('candidato')->where('id_candidato', $idCandidato)->value('cv');
                if (! $cvActual) {
                    $origen = collect(Storage::disk('public')->files('cv/candidato-4'))
                        ->first(fn ($f) => str_ends_with($f, '.pdf'));

                    if ($origen) {
                        $destino = 'cv/candidato-' . $idCandidato . '/' . uniqid('cv_') . '.pdf';
                        Storage::disk('public')->copy($origen, $destino);
                        DB::table('candidato')->where('id_candidato', $idCandidato)->update([
                            'cv' => $destino,
                            'cv_fecha_act' => now()->format('Y-m-d'),
                        ]);
                    }
                }
            }

            // Experiencias
            foreach ($c['experiencias'] as $exp) {
                $existe = DB::table('experiencia')
                    ->where('id_candidato', $idCandidato)->where('puesto', $exp['puesto'])
                    ->where('empresa', $exp['empresa'])->exists();
                if ($existe) continue;

                DB::table('experiencia')->insert([
                    'id_candidato' => $idCandidato,
                    'puesto' => $exp['puesto'],
                    'empresa' => $exp['empresa'],
                    'fecha_inicio' => now()->modify($exp['desde'])->format('Y-m-d'),
                    'fecha_fin' => $exp['hasta'] ? now()->modify($exp['hasta'])->format('Y-m-d') : null,
                    'actual' => $exp['hasta'] === null,
                    'tareas' => $exp['tareas'],
                ]);
            }

            // Títulos
            foreach ($c['titulos'] as $tit) {
                $existe = DB::table('titulo')
                    ->where('id_candidato', $idCandidato)->where('nombre', $tit['nombre'])->exists();
                if ($existe) continue;

                DB::table('titulo')->insert([
                    'id_candidato' => $idCandidato,
                    'tipo' => $tit['tipo'],
                    'nombre' => $tit['nombre'],
                    'institucion' => $tit['institucion'],
                    'fecha_inicio' => now()->modify($tit['desde'])->format('Y-m-d'),
                    'fecha_fin' => $tit['hasta'] ? now()->modify($tit['hasta'])->format('Y-m-d') : null,
                ]);
            }

            // Aptitudes
            foreach ($c['aptitudes'] as [$nombreApt, $nivel]) {
                $idAptitud = DB::table('aptitud')->whereRaw('LOWER(nombre) = LOWER(?)', [$nombreApt])->value('id_aptitud');
                if (! $idAptitud) {
                    $idAptitud = DB::table('aptitud')->insertGetId([
                        'nombre' => $nombreApt, 'tipo' => 'Habilidad', 'activo' => true,
                    ], 'id_aptitud');
                }
                DB::table('candidato_aptitud')->insertOrIgnore([
                    ['id_candidato' => $idCandidato, 'id_aptitud' => $idAptitud, 'nivel' => $nivel],
                ]);
            }

            // Idiomas
            foreach ($c['idiomas'] as [$nombreIdioma, $idNivel]) {
                $idIdioma = DB::table('idioma')->whereRaw('LOWER(nombre) = LOWER(?)', [$nombreIdioma])->value('id_idioma');
                if ($idIdioma) {
                    DB::table('candidato_idioma')->insertOrIgnore([
                        ['id_candidato' => $idCandidato, 'id_idioma' => $idIdioma, 'id_nivel' => $idNivel],
                    ]);
                }
            }

            $candidatosPorNombre[$c['nombre']] = $idCandidato;
        }

        // Juan Pérez (cuenta de pruebas del TFG) también postula a la oferta
        // Laravel Senior, como en el prototipo.
        $usuarioJuan = Usuario::query()->where('email', 'candidato@test.com')->first();
        if ($usuarioJuan) {
            $idJuan = DB::table('candidato')->where('id_usuario', $usuarioJuan->id_usuario)->value('id_candidato');
            if ($idJuan) {
                $candidatosPorNombre['Juan Pérez'] = $idJuan;
            }
        }

        // ============================================
        // 4) POSTULACIONES (+ historial)
        // ============================================
        $postulacionesDemo = [
            // [candidato, oferta, id_estado_post, fecha]
            ['Juan Pérez', 'Desarrollador Laravel Senior', 2, '-2 hours'],   // En revisión
            ['María González', 'Desarrollador Laravel Senior', 3, '-4 hours'], // Aceptada
            ['Pedro Almirón', 'Desarrollador Laravel Senior', 4, '-3 days'],  // Rechazada
            ['Carlos Ramírez', 'Analista de Sistemas', 1, '-6 hours'],        // Registrada
            ['Lucía Benítez', 'Analista de Sistemas', 2, '-1 day'],           // En revisión
        ];

        $postulacionesCreadas = [];

        foreach ($postulacionesDemo as [$nombre, $tituloOferta, $idEstado, $fecha]) {
            $idCandidato = $candidatosPorNombre[$nombre] ?? null;
            $oferta = $ofertasPorTitulo[$tituloOferta] ?? null;
            if (! $idCandidato || ! $oferta) continue;

            $existe = DB::table('postulacion')
                ->where('id_candidato', $idCandidato)->where('id_oferta', $oferta->id_oferta)->exists();
            if ($existe) continue;

            $fechaPost = now()->modify($fecha);
            $idPostulacion = DB::table('postulacion')->insertGetId([
                'id_candidato' => $idCandidato,
                'id_oferta' => $oferta->id_oferta,
                'id_estado_post' => $idEstado,
                'fecha_postulacion' => $fechaPost,
                'carta_presentacion' => 'Hola, estoy muy interesado/a en esta posición. Creo que mi perfil encaja con lo que buscan.',
            ], 'id_postulacion');

            $camino = match ($idEstado) {
                1 => [1],
                2 => [1, 2],
                3 => [1, 2, 6, 3],
                4 => [1, 2, 4],
                default => [1],
            };
            foreach ($camino as $i => $idEstadoHistorial) {
                DB::table('historial_estado')->insert([
                    'id_postulacion' => $idPostulacion,
                    'id_estado_post' => $idEstadoHistorial,
                    'fecha_cambio' => $fechaPost->copy()->addHours($i),
                    'comentario' => null,
                ]);
            }

            $postulacionesCreadas["$nombre|$tituloOferta"] = $idPostulacion;
        }

        // ============================================
        // 5) ENTREVISTAS (fecha y hora relativas a HOY)
        //    Presencial = en la sede de Tech Solutions.
        // ============================================
        $entrevistasDemo = [
            // [candidato, oferta, fecha_hora relativa, duración, modalidad, enlace, estado]
            ['Juan Pérez', 'Desarrollador Laravel Senior', 'tomorrow 10:00', 60, 'Presencial', null, 'pendiente'],
            ['María González', 'Desarrollador Laravel Senior', 'tomorrow 15:30', 60, 'Videollamada', 'https://meet.google.com/abc-defg-hij', 'pendiente'],
            ['Carlos Ramírez', 'Analista de Sistemas', '+3 days 09:00', 45, 'Presencial', null, 'pendiente'],
            ['Lucía Benítez', 'Analista de Sistemas', '+5 days 14:00', 60, 'Videollamada', 'https://meet.google.com/xyz-1234-abc', 'pendiente'],
        ];

        foreach ($entrevistasDemo as [$nombre, $tituloOferta, $cuando, $duracion, $modalidad, $enlace, $estado]) {
            $idPostulacion = $postulacionesCreadas["$nombre|$tituloOferta"] ?? null;
            if (! $idPostulacion) continue;

            $existe = DB::table('entrevista')->where('id_postulacion', $idPostulacion)->exists();
            if ($existe) continue;

            DB::table('entrevista')->insert([
                'id_postulacion' => $idPostulacion,
                'id_sede' => $modalidad === 'Presencial' ? $sede->id_sede : null,
                'fecha_hora' => now()->modify($cuando),
                'duracion_minutos' => $duracion,
                'modalidad' => $modalidad,
                'enlace_virtual' => $enlace,
                'notas' => null,
                'estado' => $estado,
                'fecha_creacion' => now(),
            ]);

            // La postulación pasa a "Entrevista" (6) al convocar.
            DB::table('postulacion')->where('id_postulacion', $idPostulacion)->update(['id_estado_post' => 6]);
            DB::table('historial_estado')->insert([
                'id_postulacion' => $idPostulacion,
                'id_estado_post' => 6,
                'fecha_cambio' => now(),
                'comentario' => 'Convocado a entrevista.',
            ]);
        }

        // ============================================
        // 6) NOTIFICACIONES del empleador
        //    (las del prototipo, como filas reales)
        // ============================================
        $notificaciones = [
            ['tipo' => 1, 'titulo' => 'Nueva postulación recibida', 'mensaje' => 'María González se postuló a Desarrollador Laravel Senior.', 'ref' => 'postulacion', 'hace' => '-2 hours', 'leida' => false, 'url' => '/empleador/postulantes'],
            ['tipo' => 1, 'titulo' => 'Nueva postulación recibida', 'mensaje' => 'Carlos Ramírez se postuló a Analista de Sistemas.', 'ref' => 'postulacion', 'hace' => '-6 hours', 'leida' => false, 'url' => '/empleador/postulantes'],
            ['tipo' => 3, 'titulo' => 'Oferta por vencer', 'mensaje' => 'Tu oferta Analista de Sistemas vence en 8 días. Considerá renovarla o cerrarla.', 'ref' => 'oferta', 'hace' => '-8 hours', 'leida' => false, 'url' => '/empleador/ofertas'],
            ['tipo' => 2, 'titulo' => 'Entrevista programada', 'mensaje' => 'Convocaste a Juan Pérez a una entrevista por Desarrollador Laravel Senior.', 'ref' => 'entrevista', 'hace' => '-1 day', 'leida' => true, 'url' => '/empleador/entrevistas'],
            ['tipo' => 1, 'titulo' => 'Postulación actualizada', 'mensaje' => 'Cambiaste el estado de la postulación de Lucía Benítez a En revisión.', 'ref' => 'postulacion', 'hace' => '-1 day', 'leida' => true, 'url' => '/empleador/postulantes'],
            ['tipo' => 3, 'titulo' => 'Oferta pausada', 'mensaje' => 'Tu oferta Diseñador UX/UI está pausada. Reactivala cuando quieras recibir postulaciones.', 'ref' => 'oferta', 'hace' => '-3 days', 'leida' => true, 'url' => '/empleador/ofertas'],
            ['tipo' => 3, 'titulo' => 'Oferta cerrada', 'mensaje' => 'Tu oferta Administrador de Base de Datos fue cerrada. Recibió varias postulaciones en total.', 'ref' => 'oferta', 'hace' => '-5 days', 'leida' => true, 'url' => '/empleador/ofertas'],
        ];

        foreach ($notificaciones as $n) {
            $existe = DB::table('notificacion')
                ->where('id_usuario', $usuarioEmpresa->id_usuario)
                ->where('titulo', $n['titulo'])
                ->where('mensaje', $n['mensaje'])
                ->exists();
            if ($existe) continue;

            DB::table('notificacion')->insert([
                'id_usuario' => $usuarioEmpresa->id_usuario,
                'id_tipo_notif' => $n['tipo'],
                'titulo' => $n['titulo'],
                'mensaje' => $n['mensaje'],
                'url_destino' => $n['url'],
                'tipo_referencia' => $n['ref'],
                'fecha' => now()->modify($n['hace']),
                'leida' => $n['leida'],
            ]);
        }

        $this->command?->info('EmployerDemoSeeder: ofertas, postulantes, postulaciones, entrevistas y notificaciones de Tech Solutions listos.');
    }
}
