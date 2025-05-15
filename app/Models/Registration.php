<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
    protected $fillable = [
        'id_student',
        'id_course',
        'id_schoolyear',
        'id_class',

    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'id_course');
    }

    public function class(): BelongsTo
    {
        return $this->belongsTo(Classes::class, 'id_class');
    }

    public function schoolyear(): BelongsTo
    {
        return $this->belongsTo(SchoolYears::class, 'id_schoolyear');
    }

    public function subjects()
    {
        return $this->belongsToMany(
            Subject::class,
            'registrations_subjects',     // ← nome exato da tua tabela pivot
            'id_registration',            // ← chave estrangeira para esta tabela
            'id_subject'                  // ← chave estrangeira para a tabela subjects
        );
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'id_student');
    }
}
