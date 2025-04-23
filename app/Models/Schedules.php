<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedules extends Model
{
    protected $fillable = [
        'id_schoolyear',
        'id_timeperiod',
        'id_room',
        'id_teacher'
    ];
}
