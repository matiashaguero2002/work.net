<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ubicacion extends Model
{
    use HasFactory;

    protected $table = 'ubicacion';

    public $timestamps = false;

    protected $primaryKey = 'id_ubicacion';

    protected $fillable = [
        'direccion',
        'ciudad',
        'provincia',
        'pais',
        'latitud',
        'longitud',
        'referencia',
    ];

    public function sedes()
    {
        return $this->hasMany(Sede::class, 'id_ubicacion', 'id_ubicacion');
    }

    public function ofertas()
    {
        return $this->hasMany(Oferta::class, 'id_ubicacion', 'id_ubicacion');
    }
}
