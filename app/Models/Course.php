<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = [
        'course',


    ];

    public function classes()
    {
        return $this->belongsTo(Classes::class, 'id_course');
    }

}
