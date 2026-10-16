<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadoPostulacion extends Model
{
    use HasFactory;

    protected $table = 'estado_postulacion';

    public $timestamps = false;

    protected $primaryKey = 'id_estado_post';

    protected $fillable = [
        'nombre',
        'descripcion',
        'orden',
    ];

    public function postulaciones()
    {
        return $this->hasMany(Postulacion::class, 'id_estado_post', 'id_estado_post');
    }

    public function historial()
    {
        return $this->hasMany(HistorialEstado::class, 'id_estado_post', 'id_estado_post');
    }
}
