<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidato extends Model
{
    use HasFactory;

    protected $table = 'candidato';

    public $timestamps = false;

    protected $primaryKey = 'id_candidato';

    protected $fillable = [
        'id_usuario',
        'fecha_nacimiento',
        'genero',
        'nacionalidad',
        'es_estudiante',
        'universidad',
        'estado_laboral',
        'perfil',
        'cv',
        'cv_fecha_act',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function experiencias()
    {
        return $this->hasMany(Experiencia::class, 'id_candidato', 'id_candidato');
    }

    public function titulos()
    {
        return $this->hasMany(Titulo::class, 'id_candidato', 'id_candidato');
    }

    public function cursos()
    {
        return $this->hasMany(Curso::class, 'id_candidato', 'id_candidato');
    }

    public function aptitudes()
    {
        return $this->belongsToMany(Aptitud::class, 'candidato_aptitud', 'id_candidato', 'id_aptitud')
            ->withPivot('nivel');
    }

    public function idiomas()
    {
        return $this->belongsToMany(Idioma::class, 'candidato_idioma', 'id_candidato', 'id_idioma')
            ->withPivot('id_nivel');
    }

    public function postulaciones()
    {
        return $this->hasMany(Postulacion::class, 'id_candidato', 'id_candidato');
    }

    public function ofertasGuardadas()
    {
        return $this->belongsToMany(Oferta::class, 'oferta_guardada', 'id_candidato', 'id_oferta');
    }
}
