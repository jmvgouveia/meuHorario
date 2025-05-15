<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseSubjects extends Model
{
    protected $fillable = [
        'id_course',
        'id_subject',
        'id_schoolyear',


    ];

    public function course()
    {
        return $this->belongsTo(Course::class, 'id_course');
    }
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'id_subject');
    }
    public function schoolyear()
    {
        return $this->belongsTo(SchoolYears::class, 'id_schoolyear');
    }



    // public function classes()
    // {
    //     return $this->hasMany(Classes::class, 'id_course'); // 'course_id' é a chave estrangeira na tabela de classes
    // }

    // public function registrarion(): HasMany
    // {
    //     return $this->hasMany(Registration::class);
    // }
    // public function subjects()
    // {
    //     return $this->hasMany(Subject::class, 'id_course');
    // }
}
