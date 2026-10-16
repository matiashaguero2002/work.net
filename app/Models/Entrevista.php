<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entrevista extends Model
{
    use HasFactory;

    protected $table = 'entrevista';

    public $timestamps = false;

    protected $primaryKey = 'id_entrevista';

    protected $fillable = [
        'id_postulacion',
        'id_sede',
        'fecha_hora',
        'duracion_minutos',
        'modalidad',
        'enlace_virtual',
        'notas',
        'estado',
        'fecha_creacion',
    ];

    public function postulacion()
    {
        return $this->belongsTo(Postulacion::class, 'id_postulacion', 'id_postulacion');
    }

    public function sede()
    {
        return $this->belongsTo(Sede::class, 'id_sede', 'id_sede');
    }
}
