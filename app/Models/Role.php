<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'guard_name',
    ];

    // Relação muitos para muitos com Permissions
    // Em Role.php
public function permissions()
{
    return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id');
}

}
