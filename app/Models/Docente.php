<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Docente extends Model
{
    protected $fillable = [
        'numero_processo',
        'name',
        'address',
        'email',
        'sigla',
        'sexo',

    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}
