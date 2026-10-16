<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Oferta extends Model
{
    use HasFactory;

    protected $table = 'oferta';

    public $timestamps = false;

    protected $primaryKey = 'id_oferta';

    protected $fillable = [
        'id_empleador',
        'id_ubicacion',
        'id_area',
        'id_modalidad',
        'id_tipo_contrato',
        'id_estado_oferta',
        'titulo',
        'descripcion',
        'requisitos',
        'forma_postulacion',
        'remuneracion_min',
        'remuneracion_max',
        'mostrar_remuneracion',
        'beneficios',
        'fecha_publicacion',
        'fecha_limite',
        'fecha_cierre',
        'vacantes',
        'visualizaciones',
        'preguntas_seleccion',
    ];

    public function empleador()
    {
        return $this->belongsTo(Empleador::class, 'id_empleador', 'id_empleador');
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class, 'id_ubicacion', 'id_ubicacion');
    }

    public function areaLaboral()
    {
        return $this->belongsTo(AreaLaboral::class, 'id_area', 'id_area');
    }

    public function modalidad()
    {
        return $this->belongsTo(ModalidadTrabajo::class, 'id_modalidad', 'id_modalidad');
    }

    public function tipoContrato()
    {
        return $this->belongsTo(TipoContrato::class, 'id_tipo_contrato', 'id_tipo_contrato');
    }

    public function estadoOferta()
    {
        return $this->belongsTo(EstadoOferta::class, 'id_estado_oferta', 'id_estado_oferta');
    }

    public function requisitos()
    {
        return $this->hasMany(RequisitoOferta::class, 'id_oferta', 'id_oferta');
    }

    public function beneficios()
    {
        return $this->hasMany(BeneficioOferta::class, 'id_oferta', 'id_oferta');
    }

    public function postulaciones()
    {
        return $this->hasMany(Postulacion::class, 'id_oferta', 'id_oferta');
    }

    public function guardadasPor()
    {
        return $this->belongsToMany(Candidato::class, 'oferta_guardada', 'id_oferta', 'id_candidato');
    }
}
