<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Student extends Model
{
    protected $fillable = [
        'studentnumber',
        'name',
        'id_gender',
        'birthdate',

    ];


    public function genders()
    {
        return $this->belongsTo(Gender::class, 'id_gender');
    }
    public function registrations()
    {
        return $this->hasMany(\App\Models\Registration::class, 'id_student', 'id');
    }
}
