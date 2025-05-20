<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedules extends Model
{
    protected $fillable = [
        'id_schoolyear',
        'id_timeperiod',
        'id_room',
        'id_teacher',
        'id_weekday',
        'id_subject',
        'turno',
        'status'
    ];

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_subjects');
    }
    public function subject()
    {
        return $this->belongsTo(Subject::class, 'id_subject');
    }


    public function weekday()
    {
        return $this->belongsTo(WeekDays::class, 'id_weekday');
    }

    public function timeperiod()
    {
        return $this->belongsTo(TimePeriod::class, 'id_timeperiod');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'id_room');
    }
    public function classes()
    {
        return $this->belongsToMany(Classes::class, 'schedules_classes', 'id_schedule', 'id_class');
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'schedules_students', 'id_schedule', 'id_student');
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'id_teacher');
    }
}
