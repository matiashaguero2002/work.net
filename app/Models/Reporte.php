<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reporte extends Model
{
    use HasFactory;

    protected $table = 'reporte';

    public $timestamps = false;

    protected $primaryKey = 'id_reporte';

    protected $fillable = [
        'id_usuario',
        'tipo',
        'motivo',
        'estado',
        'fecha_creacion',
        'fecha_resolucion',
        'id_admin_resolucion',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function adminResolucion()
    {
        return $this->belongsTo(Usuario::class, 'id_admin_resolucion', 'id_usuario');
    }
}
