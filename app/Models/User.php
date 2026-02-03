<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name','email','password','doc','phone','cep','address','role','points','theme_pref','level'
    ];

    protected $hidden = ['password','remember_token'];

    protected $casts = ['email_verified_at' => 'datetime'];

    public function courses()
    {
        return $this->hasMany(Course::class, 'created_by');
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function interactions()
    {
        return $this->hasMany(Interaction::class, 'user_from_id');
    }

    public function receivedInteractions()
    {
        return $this->hasMany(Interaction::class, 'user_to_id');
    }

    public function ranking()
    {
        return $this->hasOne(Ranking::class);
    }
}