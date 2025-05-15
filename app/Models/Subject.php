<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = [
        'subject',
        'acronym',

    ];

    public function teachers()
    {
        return $this->belongsToMany(Teacher::class, 'teacher_subjects', 'id_subject', 'id_teacher')
            ->withPivot('id_schoolyear');
    }

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_subjects', 'id_subject', 'id_course')->withTimestamps();
    }
    public function courseSubjects(): HasMany
    {
        return $this->hasMany(CourseSubjects::class, 'id_subject');
    }
}
