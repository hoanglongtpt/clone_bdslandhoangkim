<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'avatar_path',
        'birthday',
        'gender',
        'phone1',
        'phone2',
        'zalo',
        'skype',
        'facebook',
        'address',
        'bio',
        'password',
        'role',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'birthday' => 'date',
    ];

    public function projects()
    {
        return $this->belongsToMany(Project::class);
    }

    public function properties()
    {
        return $this->belongsToMany(Property::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function canEditProperties(): bool
    {
        return in_array($this->role, ['admin', 'manager'], true);
    }
}
