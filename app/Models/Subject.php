<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = [
        'subject',
        'acronym',

    ];

    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }
}
