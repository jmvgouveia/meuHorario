<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Position extends Model
{
    protected $fillable = [
       'position',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }


}
