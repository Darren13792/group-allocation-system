<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        // Listen for the 'creating' event
        static::creating(function ($user) {
            if (empty($user->password)) {
                // Set default password if none is provided
                $user->password = bcrypt('password');
            }
        });
    }

    public function student_preferences(): HasMany
    {
        return $this->hasMany(StudentPreference::class, 'student_id');
    }

    public function supervisor_preferences(): HasMany
    {
        return $this->hasMany(SupervisorPreference::class, 'supervisor_id');
    }

    public function group_students()
    {
        return $this->hasOne(GroupStudent::class, 'student_id');
    }

    public function group_supervisors()
    {
        return $this->hasMany(GroupSupervisor::class, 'supervisor_id');
    }
}
