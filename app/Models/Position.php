<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Position extends Model
{
    protected $fillable = [
       'position',
    ];

    // public function position(): BelongsTo
    // {
    //     return $this->belongsTo(Teacher::class);
    // }

    public function teacher(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }


}
