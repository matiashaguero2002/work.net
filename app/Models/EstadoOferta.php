<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EstadoOferta extends Model
{
    use HasFactory;

    protected $table = 'estado_oferta';

    public $timestamps = false;

    protected $primaryKey = 'id_estado_oferta';

    protected $fillable = [
        'nombre',
        'descripcion',
    ];

    public function ofertas()
    {
        return $this->hasMany(Oferta::class, 'id_estado_oferta', 'id_estado_oferta');
    }
}
