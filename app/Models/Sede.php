<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sede extends Model
{
    use HasFactory;

    protected $table = 'sede';

    public $timestamps = false;

    protected $primaryKey = 'id_sede';

    protected $fillable = [
        'id_empleador',
        'id_ubicacion',
        'nombre',
        'telefono',
        'horario',
    ];

    public function empleador()
    {
        return $this->belongsTo(Empleador::class, 'id_empleador', 'id_empleador');
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class, 'id_ubicacion', 'id_ubicacion');
    }

    public function entrevistas()
    {
        return $this->hasMany(Entrevista::class, 'id_sede', 'id_sede');
    }
}
