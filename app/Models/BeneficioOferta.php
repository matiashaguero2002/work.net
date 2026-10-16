<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BeneficioOferta extends Model
{
    use HasFactory;

    protected $table = 'beneficio_oferta';

    public $timestamps = false;

    protected $primaryKey = 'id_beneficio';

    protected $fillable = [
        'id_oferta',
        'descripcion',
    ];

    public function oferta()
    {
        return $this->belongsTo(Oferta::class, 'id_oferta', 'id_oferta');
    }
}
