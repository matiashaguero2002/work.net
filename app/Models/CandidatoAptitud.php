<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidatoAptitud extends Model
{
    use HasFactory;

    // Tabla pivote con PK compuesta (id_candidato + id_aptitud)
    protected $table = 'candidato_aptitud';

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = [
        'id_candidato',
        'id_aptitud',
        'nivel',
    ];

    public function candidato()
    {
        return $this->belongsTo(Candidato::class, 'id_candidato', 'id_candidato');
    }

    public function aptitud()
    {
        return $this->belongsTo(Aptitud::class, 'id_aptitud', 'id_aptitud');
    }
}
