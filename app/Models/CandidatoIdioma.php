<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidatoIdioma extends Model
{
    use HasFactory;

    // Tabla pivote con PK compuesta (id_candidato + id_idioma)
    protected $table = 'candidato_idioma';

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = [
        'id_candidato',
        'id_idioma',
        'id_nivel',
    ];

    public function candidato()
    {
        return $this->belongsTo(Candidato::class, 'id_candidato', 'id_candidato');
    }

    public function idioma()
    {
        return $this->belongsTo(Idioma::class, 'id_idioma', 'id_idioma');
    }

    public function nivel()
    {
        return $this->belongsTo(NivelIdioma::class, 'id_nivel', 'id_nivel');
    }
}
