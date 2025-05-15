<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Teacher extends Model
{
    protected $fillable = [
        'teachernumber',
        'name',
        'acronym',
        'birthdate',
        'startingdate',
        'id_nationality',
        'id_gender',
        'id_qualifications',
        'id_department',
        'id_professionalrelationship',
        'id_contractualrelationship',
        'id_salaryscale',
        'id_user',

    ];

    // relacão de um para um

    public function nationalities()
    {
        return $this->belongsTo(Nationality::class, 'id_nationality');
    }

    // public function users()
    // {
    //     return $this->belongsTo(User::class, 'id_user');
    // }


    public function user()
    {
        return $this->belongsTo(User::class, 'id_user');
    }

    public function genders()
    {
        return $this->belongsTo(Gender::class, 'id_gender');
    }

    public function qualifications()
    {
        return $this->belongsTo(Qualification::class, 'id_qualifications');
    }

    public function departments()
    {
        return $this->belongsTo(Department::class, 'id_department');
    }

    public function professionalrelationships()
    {
        return $this->belongsTo(ProfessionalRelationship::class, 'id_professionalrelationship');
    }

    public function contractualrelationship()
    {
        return $this->belongsTo(ContractualRelationship::class, 'id_contractualrelationship');
    }

    public function salaryscales()
    {
        return $this->belongsTo(SalaryScale::class, 'id_salaryscale');
    }

    public function subject()
    {
        return $this->belongsToMany(Subject::class, 'teacher_subject')->withTimestamps();
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'teacher_subject', 'teacher_id', 'subject_id');
    }


    // // relacão de muitos para muitos
    // public function teacher_position(): HasMany
    // {
    //     return $this->hasMany(Position::class);
    // }

    // public function teacher_time_reduction(): HasMany
    // {
    //     return $this->hasMany(TimeReduction::class);
    // }


}
