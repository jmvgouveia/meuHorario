<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Gender extends Model
{
    protected $fillable = [
       'gender',
    ];

    public function teacher_gender(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    // public function teacher_gender(): BelongsTo
    // {
    //     return $this->belongsTo(Student::class);
    // }

}
