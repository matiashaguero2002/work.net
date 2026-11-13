<?php

namespace Database\Seeders;

use App\Models\Candidato;
use App\Models\Empleador;
use App\Models\Sede;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsuarioSeeder extends Seeder
{
    /**
     * Usuarios de prueba para el módulo de autenticación.
     */
    public function run(): void
    {
        // Administrador
        Usuario::updateOrCreate(
            ['email' => 'admin@test.com'],
            [
                'nombre' => 'Admin General',
                'password' => Hash::make('admin123'),
                'rol' => 'admin',
                'estado' => 'activo',
                'fecha_registro' => now(),
            ]
        );

        // Candidato
        $candidato = Usuario::updateOrCreate(
            ['email' => 'candidato@test.com'],
            [
                'nombre' => 'Juan Pérez',
                'password' => Hash::make('candidato123'),
                'rol' => 'candidato',
                'estado' => 'activo',
                'fecha_registro' => now(),
            ]
        );

        Candidato::updateOrCreate(
            ['id_usuario' => $candidato->id_usuario],
            [
                'estado_laboral' => 'Buscando oportunidades',
                'perfil' => 'Candidato de prueba.',
            ]
        );

        // Empleador
        $empleador = Usuario::updateOrCreate(
            ['email' => 'empresa@test.com'],
            [
                'nombre' => 'Contacto Tech Solutions',
                'password' => Hash::make('empresa123'),
                'rol' => 'empleador',
                'estado' => 'activo',
                'fecha_registro' => now(),
            ]
        );

        $empleador = Empleador::updateOrCreate(
            ['id_usuario' => $empleador->id_usuario],
            [
                'nombre_empresa' => 'Tech Solutions S.A.',
                'ruc' => '80012345-6',
                'descripcion' => 'Tech Solutions S.A. es una empresa paraguaya dedicada al desarrollo de software a medida, consultoría tecnológica e integración de sistemas. Con más de 10 años de experiencia, hemos trabajado con clientes de diversos rubros, desde startups hasta grandes corporaciones. Nuestro equipo está conformado por profesionales apasionados por la tecnología y comprometidos con la excelencia.',
                'sitio_web' => 'www.techsolutions.com.py',
                'telefono' => '+595 71 200 300',
            ]
        );

        // Sede principal de Tech Solutions (para el perfil de empresa y las entrevistas presenciales).
        if (! $empleador->sedes()->exists()) {
            $ubicacion = \App\Models\Ubicacion::create([
                'direccion' => 'Av. Irrazábal 1234',
                'ciudad' => 'Encarnación',
                'provincia' => 'Itapúa',
                'pais' => 'Paraguay',
                'latitud' => -27.3306,
                'longitud' => -55.8667,
                'referencia' => 'A dos cuadras de la Costanera',
            ]);

            Sede::create([
                'id_empleador' => $empleador->id_empleador,
                'id_ubicacion' => $ubicacion->id_ubicacion,
                'nombre' => 'Casa Central',
                'telefono' => '+595 71 200 300',
                'horario' => 'Lun a Vie 08:00 - 17:00',
            ]);
        }
    }
}
