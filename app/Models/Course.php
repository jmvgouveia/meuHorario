<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    protected $fillable = [
        'course',


    ];

    public function classes()
    {
        return $this->hasMany(Classes::class, 'id_course'); // 'course_id' é a chave estrangeira na tabela de classes
    }

    public function registrarion(): HasMany
    {
        return $this->hasMany(Registration::class);
    }
    public function subjects()
    {
        return $this->hasMany(Subject::class, 'id_course');
    }
}
