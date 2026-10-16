<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Idioma extends Model
{
    use HasFactory;

    protected $table = 'idioma';

    public $timestamps = false;

    protected $primaryKey = 'id_idioma';

    protected $fillable = [
        'nombre',
        'codigo_iso',
    ];

    public function candidatos()
    {
        return $this->belongsToMany(Candidato::class, 'candidato_idioma', 'id_idioma', 'id_candidato')
            ->withPivot('id_nivel');
    }
}
