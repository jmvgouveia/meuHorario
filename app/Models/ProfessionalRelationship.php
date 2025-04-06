<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfessionalRelationship extends Model
{
    protected $fillable = [
       'professional_relationship',
    ];

    public function position(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }


}
