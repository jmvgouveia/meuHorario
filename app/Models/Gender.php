<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gender extends Model
{
    protected $fillable = [
        'gender',
    ];

    public function teacher(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }

    public function student(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
