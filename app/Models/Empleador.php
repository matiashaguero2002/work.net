<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empleador extends Model
{
    use HasFactory;

    protected $table = 'empleador';

    public $timestamps = false;

    protected $primaryKey = 'id_empleador';

    protected $fillable = [
        'id_usuario',
        'nombre_empresa',
        'ruc',
        'descripcion',
        'sitio_web',
        'telefono',
        'logo',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function ofertas()
    {
        return $this->hasMany(Oferta::class, 'id_empleador', 'id_empleador');
    }

    public function sedes()
    {
        return $this->hasMany(Sede::class, 'id_empleador', 'id_empleador');
    }
}
