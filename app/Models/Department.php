<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Department extends Model
{
    protected $fillable = [
       'department',
       'department_description',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }


}
