<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryScale extends Model
{
    protected $fillable = [
       'scale',
    ];

    public function teacher(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }



}
