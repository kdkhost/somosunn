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
    
    public function isAdmin()
    {
        return in_array($this->role, ['admin', 'superadmin']) || in_array($this->level, ['superadmin', 'sucesso']);
    }

    protected $fillable = [
        'name', 'email', 'password', 'doc', 'phone', 'bio', 'photo', 'cover_photo', 'role', 'points', 'theme_pref', 'level',
        // Endereço
        'cep', 'street', 'number', 'complement', 'neighborhood', 'city', 'state', 'address',
        // Redes Sociais
        'website', 'facebook', 'instagram', 'twitter', 'linkedin', 'youtube',
        // Privacidade
        'show_email_public', 'show_phone_public', 'show_address_public'
    ];

    protected $hidden = ['password','remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'social_links' => 'array'
    ];

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