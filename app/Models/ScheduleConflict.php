<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleConflict extends Model
{
    // protected $fillable = [
    //     'id_schedule_conflict',
    //     'id_schedule_novo',
    //     'id_teacher_requester',
    //     'justification',
    //     'status',
    //     'response',
    //     'response_coord',
    //     'justification_escalda',

    //     'responded_at',
    // ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class, 'id_teacher');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'id_room');
    }

    public function weekday()
    {
        return $this->belongsTo(Weekdays::class, 'id_weekday');
    }

    public function timePeriod()
    {
        return $this->belongsTo(TimePeriod::class, 'id_time_period');
    }
}
