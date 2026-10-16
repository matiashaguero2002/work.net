<?php

namespace Database\Seeders;

use App\Models\Candidato;
use App\Models\Empleador;
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

        Empleador::updateOrCreate(
            ['id_usuario' => $empleador->id_usuario],
            [
                'nombre_empresa' => 'Tech Solutions S.A.',
                'ruc' => '80012345-6',
                'descripcion' => 'Empresa de tecnología de prueba.',
            ]
        );
    }
}
