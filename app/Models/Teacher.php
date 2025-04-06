<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Teacher extends Model
{
    protected $fillable = [
        'teachernumber',
        'name',
        'acronym',
        'birthdate',
        'startingdate',

    ];

    // relacão de um para um

    public function teacher_nationality(): HasOne
    {
        return $this->hasOne(Nationality::class);
    }

    public function teacher_user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function teacher_gender(): HasOne
    {
        return $this->hasOne(Gender::class);
    }

    public function teacher_qualification(): HasOne
    {
        return $this->hasOne(Qualification::class);
    }
    public function teacher_department(): HasOne
    {
        return $this->hasOne(Department::class);
    }
    public function teacher_professionalrelationship(): HasOne
    {
        return $this->hasOne(Professional_Relationship::class);
    }
    public function teacher_contractualrelationship(): HasOne
    {
        return $this->hasOne(Contractual_Relationship::class);
    }
    public function teacher_salaryscale(): HasOne
    {
        return $this->hasOne(Salary_Scale::class);
    }


    // relacão de muitos para muitos
    public function teacher_position(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    public function teacher_time_reduction(): HasMany
    {
        return $this->hasMany(Time_Reduction::class);
    }


}
