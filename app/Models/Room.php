<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Room extends Model
{
    protected $fillable = [
        'name',
        'desciption',
        'building_id',
    ];

    public function building(): BelongsTo
    {
        return $this->belongsTo(Building::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedules::class, 'id_room');
    }


    public function isAvailableFor(string $description, string $weekday): bool
    {
        return !$this->schedules()
            ->where('description', $description)
            ->where('weekday', $weekday)
            ->exists();
    }
}
