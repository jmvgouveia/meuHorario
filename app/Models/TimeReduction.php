<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeReduction extends Model
{
    protected $fillable = [
       'time_reduction',
       'time_reduction_description',
       'time_reduction_value'
    ];


    public function time_reduction(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

}
