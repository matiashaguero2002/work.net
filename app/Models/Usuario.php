<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Usuario extends Authenticatable
{
    use HasFactory;

    protected $table = 'usuario';

    public $timestamps = false;

    protected $primaryKey = 'id_usuario';

    protected $hidden = [
        'password',
    ];

    protected $fillable = [
        'nombre',
        'email',
        'password',
        'fecha_registro',
        'estado',
        'rol',
        'ultimo_acceso',
        'intentos_fallidos',
    ];

    public function candidato()
    {
        return $this->hasOne(Candidato::class, 'id_usuario', 'id_usuario');
    }

    public function empleador()
    {
        return $this->hasOne(Empleador::class, 'id_usuario', 'id_usuario');
    }

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'id_usuario', 'id_usuario');
    }

    public function auditorias()
    {
        return $this->hasMany(Auditoria::class, 'id_usuario', 'id_usuario');
    }

    public function reportes()
    {
        return $this->hasMany(Reporte::class, 'id_usuario', 'id_usuario');
    }

    public function reportesResueltos()
    {
        return $this->hasMany(Reporte::class, 'id_admin_resolucion', 'id_usuario');
    }

    public function cambiosEstado()
    {
        return $this->hasMany(HistorialEstado::class, 'id_usuario_cambio', 'id_usuario');
    }
}
