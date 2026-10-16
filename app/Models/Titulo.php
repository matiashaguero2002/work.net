<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Titulo extends Model
{
    use HasFactory;

    protected $table = 'titulo';

    public $timestamps = false;

    protected $primaryKey = 'id_titulo';

    protected $fillable = [
        'id_candidato',
        'institucion',
        'nombre',
        'tipo',
        'fecha_inicio',
        'fecha_fin',
        'certificado',
    ];

    public function candidato()
    {
        return $this->belongsTo(Candidato::class, 'id_candidato', 'id_candidato');
    }
}
