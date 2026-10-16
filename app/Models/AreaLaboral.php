<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AreaLaboral extends Model
{
    use HasFactory;

    protected $table = 'area_laboral';

    public $timestamps = false;

    protected $primaryKey = 'id_area';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo',
    ];

    public function ofertas()
    {
        return $this->hasMany(Oferta::class, 'id_area', 'id_area');
    }
}
