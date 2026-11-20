<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CloseExpiredOffers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ofertas:cerrar-vencidas';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cierra automáticamente las ofertas activas cuya fecha_limite ya venció (RF-13).';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $ahora = now();

        $cerradas = DB::table('oferta')
            ->where('id_estado_oferta', 1) // solo activas
            ->whereNotNull('fecha_limite')
            ->where('fecha_limite', '<', $ahora)
            ->update([
                'id_estado_oferta' => 3, // Cerrada
                'fecha_cierre'     => $ahora,
            ]);

        $this->info("Se cerraron {$cerradas} oferta(s) vencida(s).");

        return static::SUCCESS;
    }
}
