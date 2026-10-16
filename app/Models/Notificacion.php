<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notificacion extends Model
{
    use HasFactory;

    protected $table = 'notificacion';

    public $timestamps = false;

    protected $primaryKey = 'id_notificacion';

    protected $fillable = [
        'id_usuario',
        'id_tipo_notif',
        'titulo',
        'mensaje',
        'url_destino',
        'id_referencia',
        'tipo_referencia',
        'fecha',
        'leida',
    ];

    public function usuario()
    {
        return $this->belongsTo(Usuario::class, 'id_usuario', 'id_usuario');
    }

    public function tipoNotificacion()
    {
        return $this->belongsTo(TipoNotificacion::class, 'id_tipo_notif', 'id_tipo_notif');
    }
}
