<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalaryScale extends Model
{
    protected $fillable = [
       'scale',
    ];

    public function qualification(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }


}
