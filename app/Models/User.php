<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use App\Models\Role;
use App\Models\Teacher;
use App\Models\UserRole;




class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */


    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // public function user_role() : HasMany
    // {
    //     return $this->hasMany(Role::class);
    // }

    // public function teacher(): HasMany
    // {
    //     return $this->hasMany(Teacher::class);
    // }
    public function teacher()
    {
        return $this->hasOne(Teacher::class, 'id_user');
    }
    // public function role()
    // {
    //     return $this->belongsTo(Role::class, 'id_role');
    // }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'id_user', 'id_role');
    }
}
