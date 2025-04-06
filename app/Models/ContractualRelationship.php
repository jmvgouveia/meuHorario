<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractualRelationship extends Model
{
    protected $fillable = [
       'contractual_relationship',
    ];

    public function contractual_relationship(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }


}
