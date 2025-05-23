<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nationality extends Model
{
    protected $fillable = [
        'nationality',
        'acronym',
    ];

    public function teacher(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }
}
