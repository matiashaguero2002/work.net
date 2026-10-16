<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Experiencia extends Model
{
    use HasFactory;

    protected $table = 'experiencia';

    public $timestamps = false;

    protected $primaryKey = 'id_experiencia';

    protected $fillable = [
        'id_candidato',
        'puesto',
        'empresa',
        'tareas',
        'fecha_inicio',
        'fecha_fin',
        'actual',
        'ubicacion',
    ];

    public function candidato()
    {
        return $this->belongsTo(Candidato::class, 'id_candidato', 'id_candidato');
    }
}
