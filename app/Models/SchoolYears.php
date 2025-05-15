<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolYears extends Model


{

    protected $table = 'schoolyears';

    protected $fillable = [
        'schoolyear',
        'start_date',
        'end_date',
        'active'
    ];

    public function registration(): HasMany
    {
        return $this->hasMany(Registration::class);
    }
    public function courseSubjects(): HasMany
    {
        return $this->hasMany(CourseSubjects::class);
    }
}
