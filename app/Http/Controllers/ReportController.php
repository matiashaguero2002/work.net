<?php

namespace App\Http\Controllers;

use App\Models\Reporte;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * RN-17 / RN-18: Guardar un reporte de oferta.
     *
     * - Recibe: id_oferta, tipo (fraud/inappropriate/misleading/spam/other),
     *   motivo texto, y id_usuario opcional del que reporta.
     * - Si id_usuario no viene (anonimato), se deja NULL.
     * - El reporte queda en estado 'pendiente' y luego el admin lo resuelve.
     * - No permite reportes duplicados idénticos (misma oferta + mismo usuario).
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'oferta_id' => 'required|integer|exists:oferta,id_oferta',
            'tipo' => 'required|string|in:fraud,inappropriate,misleading,spam,other',
            'motivo' => 'required|string|min:10',
        ], [
            'oferta_id.required' => 'La oferta es obligatoria.',
            'oferta_id.exists' => 'La oferta no existe.',
            'tipo.required' => 'El tipo de reporte es obligatorio.',
            'motivo.required' => 'El motivo es obligatorio.',
            'motivo.min' => 'El motivo debe tener al menos :min caracteres.',
        ]);

        // Evitar reportes duplicados del mismo usuario sobre la misma oferta
        if (Auth::check()) {
            $yaReporto = Reporte::where('id_usuario', Auth::id())
                ->where('id_oferta', $request->oferta_id)
                ->exists();
            if ($yaReporto) {
                return response()->json(['ok' => false, 'message' => 'Ya realizaste un reporte sobre esta oferta.'], 422);
            }
        }

        $data = [
            'id_usuario' => Auth::check() ? Auth::id() : null,
            'id_oferta' => $request->oferta_id,
            'tipo' => $request->tipo,
            'motivo' => $request->motivo,
            'estado' => 'pendiente',
        ];

        $reporte = Reporte::create($data);

        return response()->json([
            'ok' => true,
            'message' => 'Tu reporte fue enviado correctamente. Muchas gracias por ayudarnos a mejorar la plataforma.',
            'reporte_id' => $reporte->id_reporte,
        ]);
    }
}