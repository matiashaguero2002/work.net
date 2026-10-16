<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OfertaGuardada extends Model
{
    use HasFactory;

    protected $table = 'oferta_guardada';

    public $timestamps = false;

    protected $primaryKey = 'id_oferta_guardada';

    protected $fillable = [
        'id_candidato',
        'id_oferta',
        'fecha_guardado',
    ];

    public function candidato()
    {
        return $this->belongsTo(Candidato::class, 'id_candidato', 'id_candidato');
    }

    public function oferta()
    {
        return $this->belongsTo(Oferta::class, 'id_oferta', 'id_oferta');
    }
}
