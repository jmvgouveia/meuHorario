<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class TeacherPosition extends Model
{
    protected $fillable = [
    'id_teacher',
    'id_position',

    ];

    // public function position(): BelongsTo
    // {
    //     return $this->belongsTo(Teacher::class);
    // }

    public function teacher(): BelongsTo
{
    return $this->belongsTo(Teacher::class, 'id_teacher');
}
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'id_position');
    }

}
