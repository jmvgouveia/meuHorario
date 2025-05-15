<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeacherSubjects extends Model
{
    protected $fillable = [
        'id_teacher',
        'id_subject',
        'id_schoolyear',

    ];

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'id_teacher');
    }
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'id_subject');
    }
    public function schoolyear(): BelongsTo
    {
        return $this->belongsTo(SchoolYears::class, 'id_schoolyear');
    }
}
