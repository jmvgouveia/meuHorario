<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Qualification extends Model
{
    protected $fillable = [
       'qualification',
    ];

    public function qualification(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }


}
