<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BriefingDebriefing extends Model
{
    protected $table = 'briefings_debriefings';

    protected $fillable = [
        'reserva_id', 'instructor_id', 'tipo',
        'fecha_hora', 'contenido', 'areas_debiles', 'firma_instructor',
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
    ];

    /* ─── Relaciones ─── */

    public function reserva(): BelongsTo
    {
        return $this->belongsTo(Reserva::class, 'reserva_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class, 'instructor_id');
    }

    /* ─── Scopes ─── */

    public function scopePreVuelo($query)
    {
        return $query->where('tipo', 'pre_vuelo');
    }

    public function scopePostVuelo($query)
    {
        return $query->where('tipo', 'post_vuelo');
    }
}
