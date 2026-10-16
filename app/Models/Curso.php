<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Curso extends Model
{
    use HasFactory;

    protected $table = 'curso';

    public $timestamps = false;

    protected $primaryKey = 'id_curso';

    protected $fillable = [
        'id_candidato',
        'institucion',
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'certificado',
        'horas',
    ];

    public function candidato()
    {
        return $this->belongsTo(Candidato::class, 'id_candidato', 'id_candidato');
    }
}
