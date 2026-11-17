<?php

namespace App\Services;

use App\Models\Entrevista;
use App\Models\Notificacion;
use Carbon\Carbon;

/**
 * Chat de reagendamiento (RN-19): las propuestas viven en entrevista.notas.
 *
 * Formato de una línea de propuesta:
 *   [Propuesta del candidato]: dd/mm/YYYY a las HH:MM hs — "mensaje" (enviado dd/mm/YYYY HH:MM)
 *
 * Líneas de estado que pueden seguirle (con su fecha):
 *   [Visto por el empleador] dd/mm/YYYY HH:MM
 *   [Aceptada por el candidato] dd/mm/YYYY HH:MM
 *   [Rechazada por el candidato] dd/mm/YYYY HH:MM
 */
class PropuestaEntrevista
{
    public const CANDIDATO = 'candidato';
    public const EMPLEADOR = 'empleador';

    /**
     * Última propuesta registrada (ignora líneas de Visto posteriores).
     * Devuelve ['autor' => 'candidato'|'empleador', 'fecha' => 'dd/mm/YYYY',
     *           'hora' => 'HH:MM', 'i' => índice de línea] o null.
     */
    public static function ultima(?string $notas): ?array
    {
        $lineas = explode("\n", trim($notas ?? ''));

        for ($i = count($lineas) - 1; $i >= 0; $i--) {
            $linea = trim($lineas[$i]);

            if (preg_match('/^\[Visto por /', $linea)) {
                continue; // recibo de lectura: no tapa la propuesta
            }

            if (preg_match('/^\[Propuesta del (candidato|empleador)\]:/', $linea, $m)) {
                if (preg_match('/(\d{2}\/\d{2}\/\d{4}) a las (\d{2}:\d{2})/', $linea, $f)) {
                    return ['autor' => $m[1], 'fecha' => $f[1], 'hora' => $f[2], 'i' => $i];
                }

                return ['autor' => $m[1], 'fecha' => null, 'hora' => null, 'i' => $i];
            }

            // Cualquier otra línea (aceptada/rechazada) cierra la búsqueda.
            break;
        }

        return null;
    }

    /**
     * ¿El remitente ya tiene una propuesta esperando respuesta? (punto 5:
     * evita propuestas encadenadas hasta que el otro responda).
     */
    public static function propuestaPendiente(?string $notas, string $autor): bool
    {
        $ultima = self::ultima($notas);

        return $ultima !== null && $ultima['autor'] === $autor;
    }

    /**
     * Punto 4 (recibo de lectura): marca la última propuesta ajena como
     * vista. Idempotente: no duplica el "[Visto por …]".
     */
    public static function marcarVisto(Entrevista $entrevista, string $quien): void
    {
        $notas = trim($entrevista->notas ?? '');
        if ($notas === '') {
            return;
        }

        $lineas = explode("\n", $notas);
        $ultima = self::ultima($notas);

        if ($ultima === null || $ultima['autor'] === $quien || $ultima['fecha'] === null) {
            return; // nada ajeno que marcar
        }

        // ¿Ya existe un "[Visto por {quien}]" posterior a esa propuesta?
        for ($i = $ultima['i'] + 1; $i < count($lineas); $i++) {
            if (str_starts_with(trim($lineas[$i]), '[Visto por ' . self::etiqueta($quien) . ']')) {
                return;
            }
        }

        $lineas[] = '[Visto por ' . self::etiqueta($quien) . '] ' . now()->locale('es')->translatedFormat('d/m/Y H:i');
        $entrevista->notas = implode("\n", $lineas);
        $entrevista->save();
    }

    /**
     * Acepta la última propuesta (enviada por el otro):
     * mueve fecha_hora, vuelve a "pendiente", registra y notifica.
     */
    public static function aceptar(Entrevista $entrevista, string $quien): array
    {
        $ultima = self::ultima($entrevista->notas);

        if ($ultima === null || $ultima['autor'] === $quien || $ultima['fecha'] === null) {
            return ['ok' => false, 'message' => 'No hay ninguna propuesta pendiente de respuesta.'];
        }

        $nueva = Carbon::createFromFormat('d/m/Y H:i', $ultima['fecha'] . ' ' . $ultima['hora']);

        $entrevista->fecha_hora = $nueva;
        $entrevista->estado = 'pendiente';
        $entrevista->notas = trim($entrevista->notas ?? '') . "\n[Aceptada por " . self::etiqueta($quien) . '] ' . now()->locale('es')->translatedFormat('d/m/Y H:i');
        $entrevista->save();

        $oferta = $entrevista->postulacion->oferta->titulo ?? 'la oferta';
        self::notificarAlOtro($entrevista, $quien, 'Reagendamiento confirmado', ucfirst(self::etiqueta($quien))
            . ' aceptó tu propuesta: la entrevista por ' . $oferta . ' quedó para el '
            . $nueva->locale('es')->translatedFormat('d/m/Y') . ' a las ' . $ultima['hora'] . ' hs.');

        return ['ok' => true, 'message' => 'Propuesta aceptada. La entrevista quedó reprogramada para el '
            . $nueva->locale('es')->translatedFormat('d/m/Y') . ' a las ' . $ultima['hora'] . ' hs.'];
    }

    /**
     * Rechaza la última propuesta (enviada por el otro):
     * mantiene la fecha original, vuelve a "pendiente", registra y notifica.
     */
    public static function rechazar(Entrevista $entrevista, string $quien): array
    {
        $ultima = self::ultima($entrevista->notas);

        if ($ultima === null || $ultima['autor'] === $quien) {
            return ['ok' => false, 'message' => 'No hay ninguna propuesta pendiente de respuesta.'];
        }

        $original = Carbon::parse($entrevista->fecha_hora);

        $entrevista->estado = 'pendiente';
        $entrevista->notas = trim($entrevista->notas ?? '') . "\n[Rechazada por " . self::etiqueta($quien) . '] ' . now()->locale('es')->translatedFormat('d/m/Y H:i');
        $entrevista->save();

        $oferta = $entrevista->postulacion->oferta->titulo ?? 'la oferta';
        self::notificarAlOtro($entrevista, $quien, 'Propuesta rechazada', ucfirst(self::etiqueta($quien))
            . ' rechazó tu propuesta de reagendamiento para ' . $oferta
            . '; la entrevista mantiene su fecha original (el '
            . $original->locale('es')->translatedFormat('d/m/Y') . ' a las ' . $original->format('H:i') . ' hs).');

        return ['ok' => true, 'message' => 'Propuesta rechazada. La entrevista mantiene su fecha original.'];
    }

    /** 'candidato' → 'el candidato', 'empleador' → 'el empleador'. */
    private static function etiqueta(string $quien): string
    {
        return $quien === self::CANDIDATO ? 'el candidato' : 'el empleador';
    }

    /** Notifica a la parte OPUESTA a la que actuó. */
    private static function notificarAlOtro(Entrevista $entrevista, string $quien, string $titulo, string $mensaje): void
    {
        if ($quien === self::CANDIDATO) {
            $idUsuario = $entrevista->postulacion?->oferta?->empleador?->id_usuario;
        } else {
            $idUsuario = $entrevista->postulacion?->candidato?->id_usuario;
        }

        if (! $idUsuario) {
            return;
        }

        Notificacion::query()->create([
            'id_usuario' => $idUsuario,
            'id_tipo_notif' => 2, // Entrevista
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'url_destino' => $quien === self::CANDIDATO ? '/empleador/entrevistas' : '/entrevistas',
            'tipo_referencia' => 'entrevista',
            'id_referencia' => $entrevista->id_entrevista,
            'fecha' => now(),
            'leida' => false,
        ]);
    }
}
