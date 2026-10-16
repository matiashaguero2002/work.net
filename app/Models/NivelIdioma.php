<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NivelIdioma extends Model
{
    use HasFactory;

    protected $table = 'nivel_idioma';

    public $timestamps = false;

    protected $primaryKey = 'id_nivel';

    protected $fillable = [
        'nombre',
        'orden',
    ];

    public function candidatos()
    {
        return $this->belongsToMany(Candidato::class, 'candidato_idioma', 'id_nivel', 'id_candidato');
    }
}
