<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleRequest extends Model
{
    protected $fillable = [
        'id_schedule_conflict',
        'id_schedule_novo',
        'id_teacher_requester',
        'justification',
        'status',
        'response',
        'response_coord',
        'justification_escalda',

        'responded_at',
    ];

    public function scheduleConflict(): BelongsTo
    {
        return $this->belongsTo(Schedules::class, 'id_schedule_conflict');
    }

    public function scheduleNovo(): BelongsTo
    {
        return $this->belongsTo(Schedules::class, 'id_schedule_novo');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(Teacher::class, 'id_teacher_requester');
    }
    public function teacher()
{
    return $this->belongsTo(Teacher::class, 'id_teacher');
}
}
