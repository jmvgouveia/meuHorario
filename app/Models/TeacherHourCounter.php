<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class TeacherHourCounter extends Model
{
    protected $fillable = [
    'id_teacher',
    'carga_horaria',
    'carga_componente_letiva',
    'carga_componente_naoletiva',
    'autorizado_horas_extra',
    'autorizado_horas_extra',

    ];

    // public function position(): BelongsTo
    // {
    //     return $this->belongsTo(Teacher::class);
    // }

    public function teacher(): BelongsTo
{
    return $this->belongsTo(Teacher::class, 'id_teacher');
}
 

}
