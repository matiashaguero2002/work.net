<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistorialEstado extends Model
{
    use HasFactory;

    protected $table = 'historial_estado';

    public $timestamps = false;

    protected $primaryKey = 'id_historial';

    protected $fillable = [
        'id_postulacion',
        'id_estado_post',
        'fecha_cambio',
        'comentario',
        'id_usuario_cambio',
    ];

    public function postulacion()
    {
        return $this->belongsTo(Postulacion::class, 'id_postulacion', 'id_postulacion');
    }

    public function estadoPostulacion()
    {
        return $this->belongsTo(EstadoPostulacion::class, 'id_estado_post', 'id_estado_post');
    }

    public function usuarioCambio()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario_cambio', 'id_usuario');
    }
}
