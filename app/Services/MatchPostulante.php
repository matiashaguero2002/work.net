<?php

namespace App\Services;

use App\Models\Candidato;
use App\Models\Oferta;

/**
 * Helper para el % de match de un postulante con una oferta.
 * Usa el MISMO algoritmo que el recomendador de ofertas del
 * candidato (OfertaRecomendador), así el porcentaje que ve el
 * empleador es exactamente el que ve el postulante.
 */
class MatchPostulante
{
    /**
     * Puntaje 0-100 del candidato para la oferta.
     */
    public static function puntaje(?Candidato $candidato, ?Oferta $oferta): int
    {
        if (! $candidato || ! $oferta) {
            return 0;
        }

        $senales = OfertaRecomendador::senales($candidato);
        if ($senales === null) {
            return 0; // Perfil vacío: sin datos suficientes.
        }

        return OfertaRecomendador::scoreOferta($senales, $oferta)['score'];
    }
}
