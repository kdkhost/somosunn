<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'phone', 'photo'
    ];

    protected $hidden = ['password'];

    public function children() {
        return $this->hasMany(Child::class, 'responsible_id');
    }

    public function sponsors() {
        return $this->hasMany(Sponsor::class);
    }
}
