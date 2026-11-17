<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UsuarioSeeder::class,
            // Datos demo del módulo empleador (Tech Solutions) y del mapa.
            EmployerDemoSeeder::class,
        ]);
    }
}
