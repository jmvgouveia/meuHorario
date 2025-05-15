<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Classes extends Model
{
    protected $fillable = [
        'class',
        'id_course',


    ];



    // public function courses(): HasMany
    // {
    //     return $this->hasMany(Course::class);
    // }


    public function course()
    {
        return $this->belongsTo(Course::class, 'id_course'); // 'course_id' é a chave estrangeira na tabela de classes
    }

    public function classes()
    {
        return $this->belongsToMany(
            \App\Models\Classes::class,
            'schedule_class',      // nome da tabela pivot
            'schedule_id',
            'class_id'
        );
    }
}
