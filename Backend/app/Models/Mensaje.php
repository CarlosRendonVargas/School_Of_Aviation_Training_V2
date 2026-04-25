<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mensaje extends Model
{
    protected $fillable = [
        'remitente_id', 'destinatario_id', 'asunto', 'cuerpo',
        'leido', 'leido_en', 'respuesta_a',
    ];

    protected $casts = ['leido' => 'boolean', 'leido_en' => 'datetime'];

    public function remitente()    { return $this->belongsTo(Usuario::class, 'remitente_id'); }
    public function destinatario() { return $this->belongsTo(Usuario::class, 'destinatario_id'); }
    public function hilo()         { return $this->belongsTo(Mensaje::class, 'respuesta_a'); }
    public function respuestas()   { return $this->hasMany(Mensaje::class, 'respuesta_a'); }
}
