<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'guard_name',
    ];

//     public function role(): BelongsTo
//     {
//         return $this->belongsTo(User::class);
//     }

// Relacionamento muitos para muitos com roles
// public function roles()
// {
//     return $this->belongsToMany(Role::class, 'role_permission');
// }

public function roles()
{
    return $this->belongsToMany(Role::class);
}
}
