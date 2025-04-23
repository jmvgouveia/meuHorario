<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeekDays extends Model
{

    protected $table = 'weekdays';


    protected $fillable = [
        'weekday',
    ];
}
