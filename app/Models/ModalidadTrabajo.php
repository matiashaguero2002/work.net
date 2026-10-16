<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModalidadTrabajo extends Model
{
    use HasFactory;

    protected $table = 'modalidad_trabajo';

    public $timestamps = false;

    protected $primaryKey = 'id_modalidad';

    protected $fillable = [
        'nombre',
        'activo',
    ];

    public function ofertas()
    {
        return $this->hasMany(Oferta::class, 'id_modalidad', 'id_modalidad');
    }
}
