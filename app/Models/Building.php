<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Building extends Model
{

    protected $primaryKey = 'id'; // Nome da chave primária se não for `id`
    public $incrementing = true; // Define que a chave é auto-incrementada

    protected $fillable = [
        'name',
        'acronym',
        'address',
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}
