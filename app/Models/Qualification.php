<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Qualification extends Model
{
    protected $fillable = [
       'qualification',
    ];

    public function teacher(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }



}
