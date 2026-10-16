<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Postulacion extends Model
{
    use HasFactory;

    protected $table = 'postulacion';

    public $timestamps = false;

    protected $primaryKey = 'id_postulacion';

    protected $fillable = [
        'id_candidato',
        'id_oferta',
        'fecha_postulacion',
        'id_estado_post',
        'observaciones',
        'cv_adjunto',
        'carta_presentacion',
        'respuestas_preguntas',
    ];

    public function candidato()
    {
        return $this->belongsTo(Candidato::class, 'id_candidato', 'id_candidato');
    }

    public function oferta()
    {
        return $this->belongsTo(Oferta::class, 'id_oferta', 'id_oferta');
    }

    public function estadoPostulacion()
    {
        return $this->belongsTo(EstadoPostulacion::class, 'id_estado_post', 'id_estado_post');
    }

    public function entrevistas()
    {
        return $this->hasMany(Entrevista::class, 'id_postulacion', 'id_postulacion');
    }

    public function historialEstados()
    {
        return $this->hasMany(HistorialEstado::class, 'id_postulacion', 'id_postulacion');
    }
}
