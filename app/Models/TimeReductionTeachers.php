<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimeReductionTeachers extends Model
{
    protected $fillable = [
        'id_teacher',
        'id_time_reduction',
    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'id_teacher');
    }
    public function timeReduction(): BelongsTo
    {
        return $this->belongsTo(TimeReduction::class, 'id_time_reduction');
    }

}
