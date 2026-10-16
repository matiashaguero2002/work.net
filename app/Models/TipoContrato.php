<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoContrato extends Model
{
    use HasFactory;

    protected $table = 'tipo_contrato';

    public $timestamps = false;

    protected $primaryKey = 'id_tipo_contrato';

    protected $fillable = [
        'nombre',
        'activo',
    ];

    public function ofertas()
    {
        return $this->hasMany(Oferta::class, 'id_tipo_contrato', 'id_tipo_contrato');
    }
}
