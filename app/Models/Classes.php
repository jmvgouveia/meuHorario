<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Classes extends Model
{
    protected $fillable = [
        'class',
        'App\Models\Course',


    ];



    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }


    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class , 'App\Models\Course');
    }






}
