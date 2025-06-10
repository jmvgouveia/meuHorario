<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TimeReduction extends Model
{
    protected $fillable = [
        'time_reduction',
        'time_reduction_description',
        'time_reduction_value',
        'time_reduction_value_nl',
        'eligibility'
    ];


    public function teacher(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }

    public function timeReduction(): BelongsTo
    {
        return $this->belongsTo(TimeReduction::class, 'id_time_reduction');
    }
}
