<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Department extends Model
{
    protected $fillable = [
       'department',
       'department_description',
    ];

    public function teacher(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }



}
