<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequisitoOferta extends Model
{
    use HasFactory;

    protected $table = 'requisito_oferta';

    public $timestamps = false;

    protected $primaryKey = 'id_requisito';

    protected $fillable = [
        'id_oferta',
        'id_tipo_req',
        'descripcion',
        'obligatorio',
    ];

    public function oferta()
    {
        return $this->belongsTo(Oferta::class, 'id_oferta', 'id_oferta');
    }

    public function tipoRequisito()
    {
        return $this->belongsTo(TipoRequisito::class, 'id_tipo_req', 'id_tipo_req');
    }
}
