<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aptitud extends Model
{
    use HasFactory;

    protected $table = 'aptitud';

    public $timestamps = false;

    protected $primaryKey = 'id_aptitud';

    protected $fillable = [
        'nombre',
        'tipo',
        'activo',
    ];

    public function candidatos()
    {
        return $this->belongsToMany(Candidato::class, 'candidato_aptitud', 'id_aptitud', 'id_candidato')
            ->withPivot('nivel');
    }
}
