<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoNotificacion extends Model
{
    use HasFactory;

    protected $table = 'tipo_notificacion';

    public $timestamps = false;

    protected $primaryKey = 'id_tipo_notif';

    protected $fillable = [
        'nombre',
        'plantilla',
    ];

    public function notificaciones()
    {
        return $this->hasMany(Notificacion::class, 'id_tipo_notif', 'id_tipo_notif');
    }
}
