<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * OFERTAS DEMO del mapa (RF-07 / RF-06).
 *
 * Contenido EXACTAMENTE igual a los mocks de MapController
 * (mismas 7 ofertas, mismos textos, mismas coordenadas) para que
 * el mapa siga mostrando lo mismo, pero persistido en la base de
 * datos Work_net (oferta, ubicacion, empleador, usuario, catálogos
 * y oferta_guardada).
 *
 * Es idempotente: se puede ejecutar varias veces sin duplicar
 * (usa firstOrCreate / exists() en cada paso).
 *
 * IMPORTANTE: no altera la ESTRUCTURA de la BD; solo inserta filas.
 */
class DemoOfertaSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================
        // 1) CATÁLOGOS (id explícitos, usados por oferta)
        // ============================================
        DB::table('area_laboral')->insertOrIgnore([
            ['id_area' => 1, 'nombre' => 'Informática / TI', 'descripcion' => 'Desarrollo, soporte, infraestructura y datos.', 'activo' => true],
            ['id_area' => 2, 'nombre' => 'Diseño', 'descripcion' => 'Diseño de producto, UX/UI y gráfico.', 'activo' => true],
            ['id_area' => 3, 'nombre' => 'Marketing', 'descripcion' => 'Marketing digital, contenido y comunidades.', 'activo' => true],
            ['id_area' => 4, 'nombre' => 'Administración', 'descripcion' => 'Administración, contabilidad y finanzas.', 'activo' => true],
        ]);

        DB::table('modalidad_trabajo')->insertOrIgnore([
            ['id_modalidad' => 1, 'nombre' => 'Presencial', 'activo' => true],
            ['id_modalidad' => 2, 'nombre' => 'Remoto', 'activo' => true],
            ['id_modalidad' => 3, 'nombre' => 'Híbrido', 'activo' => true],
        ]);

        DB::table('tipo_contrato')->insertOrIgnore([
            ['id_tipo_contrato' => 1, 'nombre' => 'Tiempo completo', 'activo' => true],
            ['id_tipo_contrato' => 2, 'nombre' => 'Medio tiempo', 'activo' => true],
            ['id_tipo_contrato' => 3, 'nombre' => 'Pasantía', 'activo' => true],
            ['id_tipo_contrato' => 4, 'nombre' => 'Por contrato', 'activo' => true],
            ['id_tipo_contrato' => 5, 'nombre' => 'Freelance', 'activo' => true],
        ]);

        DB::table('estado_oferta')->insertOrIgnore([
            ['id_estado_oferta' => 1, 'nombre' => 'Activa', 'descripcion' => 'Publicada y aceptando postulaciones.'],
            ['id_estado_oferta' => 2, 'nombre' => 'Pausada', 'descripcion' => 'Temporalmente sin recibir postulaciones.'],
            ['id_estado_oferta' => 3, 'nombre' => 'Cerrada', 'descripcion' => 'Finalizó el proceso de selección.'],
        ]);

        // ============================================
        // 2) EMPRESAS DEMO (usuario + empleador)
        //    Son las empresas de las 7 ofertas del mapa.
        // ============================================
        $empresas = [
            'sanmiguel'   => ['Concesionaria San Miguel', '80012345-1'],
            'datacorp'    => ['DataCorp S.A.', '80012346-2'],
            'techhelp'    => ['Tech Help Paraguay', '80012347-3'],
            'creative'    => ['Creative Studio', '80012348-4'],
            'bancoreg'    => ['Banco Regional', '80012349-5'],
            'appfactory'  => ['App Factory', '80012350-6'],
            'cloudsol'    => ['Cloud Solutions', '80012351-7'],
        ];

        $empleadorPorOferta = [];
        foreach ($empresas as $slug => [$nombre, $ruc]) {
            $usuario = \App\Models\Usuario::firstOrCreate(
                ['email' => "rrhh.{$slug}@demo.worknet"],
                [
                    'nombre' => "Estudios y selección - {$nombre}",
                    'password' => Hash::make('empresa-demo'),
                    'rol' => 'empleador',
                    'estado' => 'activo',
                ]
            );

            $empleador = \App\Models\Empleador::firstOrCreate(
                ['id_usuario' => $usuario->id_usuario],
                [
                    'nombre_empresa' => $nombre,
                    'ruc' => $ruc,
                ]
            );

            $empleadorPorOferta[$nombre] = $empleador->id_empleador;
        }

        // ============================================
        // 3) UBICACIONES + OFERTAS (ids 1..7 fijos para
        //    que coincidan con los mocks del mapa)
        // ============================================
        $ofertas = [
            [
                'id_oferta' => 1,
                'empresa' => 'Concesionaria San Miguel',
                'titulo' => 'Desarrollador Full Stack Laravel',
                'descripcion' => 'Estamos en búsqueda de un Desarrollador Full Stack con sólida experiencia en PHP 8.3 y Laravel 12 para integrarse a nuestro equipo de tecnología. La persona seleccionada será responsable del desarrollo y mantenimiento de aplicaciones web internas, la integración con APIs de terceros y la optimización del rendimiento de los sistemas existentes.',
                'requisitos' => "PHP 8.3+\nLaravel 12\nMySQL / PostgreSQL\nJavaScript\nGit\nAPIs REST",
                'forma_postulacion' => 'rrhh@sanmiguel.com.py | +595 71 200 300',
                'remuneracion_min' => 7000000,
                'remuneracion_max' => 9000000,
                'mostrar_remuneracion' => true,
                'beneficios' => 'Seguro médico, capacitaciones continuas, horario flexible, ambiente laboral agradable, oportunidades de crecimiento.',
                'publicada' => '-2 days',
                'coords' => [-27.3306, -55.8667],
                'direccion' => 'Avda. Mcal. López 515',
                'id_area' => 1,
                'id_modalidad' => 1, // Presencial
                'id_tipo_contrato' => 1, // Tiempo completo
            ],
            [
                'id_oferta' => 2,
                'empresa' => 'DataCorp S.A.',
                'titulo' => 'Analista de Sistemas',
                'descripcion' => 'Buscamos Analista de Sistemas para liderar el relevamiento de requerimientos y la documentación funcional de proyectos de transformación digital.',
                'requisitos' => "Análisis funcional\nUML\nSQL\nMetodologías ágiles\nDocumentación",
                'forma_postulacion' => 'contacto@datacorp.com.py',
                'remuneracion_min' => 6000000,
                'remuneracion_max' => 8000000,
                'mostrar_remuneracion' => true,
                'beneficios' => 'Seguro médico, bonos por objetivos, capacitación.',
                'publicada' => '-5 days',
                'coords' => [-27.3256, -55.8600],
                'direccion' => 'Mariscal Estigarribia 321',
                'id_area' => 1,
                'id_modalidad' => 3, // Híbrido
                'id_tipo_contrato' => 1,
            ],
            [
                'id_oferta' => 3,
                'empresa' => 'Tech Help Paraguay',
                'titulo' => 'Soporte Técnico IT',
                'descripcion' => 'Estamos buscando un Técnico de Soporte para atender consultas y resolver incidencias técnicas de usuarios internos y externos.',
                'requisitos' => "Redes\nWindows\nLinux\nAtención al cliente",
                'forma_postulacion' => 'soporte@techhelp.com.py',
                'remuneracion_min' => 3500000,
                'remuneracion_max' => 4500000,
                'mostrar_remuneracion' => true,
                'beneficios' => 'Capacitaciones, transporte, buen clima laboral.',
                'publicada' => '-7 days',
                'coords' => [-27.3400, -55.8750],
                'direccion' => 'Cerro Corá 892',
                'id_area' => 1,
                'id_modalidad' => 1,
                'id_tipo_contrato' => 2, // Medio tiempo
            ],
            [
                'id_oferta' => 4,
                'empresa' => 'Creative Studio',
                'titulo' => 'Diseñador UX/UI',
                'descripcion' => 'Buscamos Diseñador UX/UI para crear experiencias digitales memorables.',
                'requisitos' => "Figma\nAdobe XD\nPortafolio\nDesign Thinking",
                'forma_postulacion' => 'hola@creativestudio.com',
                'remuneracion_min' => null,
                'remuneracion_max' => null,
                'mostrar_remuneracion' => false, // "A convenir"
                'beneficios' => 'Trabajo 100% remoto, horario flexible, proyectos variados.',
                'publicada' => '-3 days',
                'coords' => [-27.3180, -55.8580],
                'direccion' => 'Padre Kreusser 120',
                'id_area' => 2, // Diseño
                'id_modalidad' => 2, // Remoto
                'id_tipo_contrato' => 5, // Freelance
            ],
            [
                'id_oferta' => 5,
                'empresa' => 'Banco Regional',
                'titulo' => 'Administrador de Base de Datos',
                'descripcion' => 'El Banco Regional busca un Administrador de Base de Datos con experiencia en PostgreSQL para gestionar la infraestructura de datos crítica.',
                'requisitos' => "PostgreSQL\nReplicación\nBackups\nTuning\nAlta disponibilidad",
                'forma_postulacion' => 'talento@bancoregional.com.py',
                'remuneracion_min' => 8000000,
                'remuneracion_max' => 10000000,
                'mostrar_remuneracion' => true,
                'beneficios' => 'Seguro médico familiar, bonos anuales, estabilidad laboral.',
                'publicada' => '-1 day',
                'coords' => [-27.3350, -55.8700],
                'direccion' => 'Avda. España 456',
                'id_area' => 1,
                'id_modalidad' => 1,
                'id_tipo_contrato' => 1,
            ],
            [
                'id_oferta' => 6,
                'empresa' => 'App Factory',
                'titulo' => 'Desarrollador Mobile Flutter',
                'descripcion' => 'App Factory busca un Desarrollador Mobile con experiencia en Flutter para crear aplicaciones móviles innovadoras.',
                'requisitos' => "Flutter\nDart\nFirebase\nREST APIs\nGit",
                'forma_postulacion' => 'jobs@appfactory.com',
                'remuneracion_min' => 6500000,
                'remuneracion_max' => 8500000,
                'mostrar_remuneracion' => true,
                'beneficios' => '100% remoto, capacitaciones, equipos modernos.',
                'publicada' => '-4 days',
                'coords' => [-27.3280, -55.8800],
                'direccion' => 'Boquerón 155',
                'id_area' => 1,
                'id_modalidad' => 2,
                'id_tipo_contrato' => 1,
            ],
            [
                'id_oferta' => 7,
                'empresa' => 'Cloud Solutions',
                'titulo' => 'Ingeniero DevOps',
                'descripcion' => 'Buscamos Ingeniero DevOps para automatizar procesos de despliegue, gestionar infraestructura en la nube y garantizar la disponibilidad de los servicios.',
                'requisitos' => "Docker\nKubernetes\nAWS\nTerraform\nCI/CD",
                'forma_postulacion' => 'rrhh@cloudsolutions.com.py',
                'remuneracion_min' => 9000000,
                'remuneracion_max' => 12000000,
                'mostrar_remuneracion' => true,
                'beneficios' => 'Seguro médico, home office, bonos por desempeño.',
                'publicada' => '-6 hours',
                'coords' => [-27.3220, -55.8650],
                'direccion' => 'Independencia Nacional 77',
                'id_area' => 1,
                'id_modalidad' => 3,
                'id_tipo_contrato' => 1,
            ],
        ];

        foreach ($ofertas as $o) {
            if (DB::table('oferta')->where('id_oferta', $o['id_oferta'])->exists()) {
                continue; // idempotente: nunca sobrescribir datos del usuario
            }

            $ubicacionId = DB::table('ubicacion')->insertGetId([
                'direccion' => $o['direccion'] . ', ' . 'Encarnación',
                'ciudad' => 'Encarnación',
                'provincia' => 'Itapúa',
                'pais' => 'Paraguay',
                'latitud' => $o['coords'][0],
                'longitud' => $o['coords'][1],
                'referencia' => $o['empresa'],
            ], 'id_ubicacion');

            DB::table('oferta')->insert([
                'id_oferta' => $o['id_oferta'],
                'id_empleador' => $empleadorPorOferta[$o['empresa']],
                'id_ubicacion' => $ubicacionId,
                'id_area' => $o['id_area'],
                'id_modalidad' => $o['id_modalidad'],
                'id_tipo_contrato' => $o['id_tipo_contrato'],
                'id_estado_oferta' => 1, // Activa
                'titulo' => $o['titulo'],
                'descripcion' => $o['descripcion'],
                'requisitos' => $o['requisitos'],
                'forma_postulacion' => $o['forma_postulacion'],
                'remuneracion_min' => $o['remuneracion_min'],
                'remuneracion_max' => $o['remuneracion_max'],
                'mostrar_remuneracion' => $o['mostrar_remuneracion'],
                'beneficios' => $o['beneficios'],
                'vacantes' => 1,
                'visualizaciones' => 0,
                'fecha_publicacion' => now()->add($o['publicada']),
            ]);
        }

        // ============================================
        // 4) OFERTAS GUARDADAS DEMO para el candidato
        //    "Matias Aguero" (el mismo set del mock
        //    original de /candidato/guardadas).
        // ============================================
        $matias = \App\Models\Usuario::where('email', 'matiasoycapo5@gmail.com')->first();
        if ($matias && $matias->candidato) {
            $idCandidato = $matias->candidato->id_candidato;
            $guardados = [
                1 => '-2 days',
                5 => '-7 days',
                6 => '-14 days',
                7 => '-4 days',
            ];
            foreach ($guardados as $idOferta => $desde) {
                $existe = DB::table('oferta_guardada')
                    ->where('id_candidato', $idCandidato)
                    ->where('id_oferta', $idOferta)
                    ->exists();
                if ($existe) {
                    continue;
                }
                DB::table('oferta_guardada')->insert([
                    'id_candidato' => $idCandidato,
                    'id_oferta' => $idOferta,
                    'fecha_guardado' => now()->add($desde),
                ]);
            }
        }

        $this->command?->info('DemoOfertaSeeder: catálogos, 7 empresas demo, 7 ofertas (ids 1-7) y guardados demo listos.');
    }
}
