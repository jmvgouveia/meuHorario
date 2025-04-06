<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nationality extends Model
{
    protected $fillable = [
       'nationality',
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
