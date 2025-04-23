<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolYears extends Model


{

    protected $table = 'schoolyears';

    protected $fillable = [
       'schoolyear',
         'start_date',
            'end_date',
            'active'
    ];




}
