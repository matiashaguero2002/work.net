<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoRequisito extends Model
{
    use HasFactory;

    protected $table = 'tipo_requisito';

    public $timestamps = false;

    protected $primaryKey = 'id_tipo_req';

    protected $fillable = [
        'nombre',
    ];

    public function requisitos()
    {
        return $this->hasMany(RequisitoOferta::class, 'id_tipo_req', 'id_tipo_req');
    }
}
