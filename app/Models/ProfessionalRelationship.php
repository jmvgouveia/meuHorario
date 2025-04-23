<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class ProfessionalRelationship extends Model
{
    protected $fillable = [
       'professional_relationship',
    ];

    public function teacher(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }



}
