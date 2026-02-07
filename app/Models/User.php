<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, Traits\HasFeatureAccess;

    public function isAdmin()
    {
        return in_array($this->role, ['admin', 'superadmin']) || in_array($this->level, ['superadmin', 'sucesso']);
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'doc',
        'phone',
        'bio',
        'occupation',
        'company',
        'photo',
        'cover_photo',
        'role',
        'points',
        'theme_pref',
        'level',
        'plan_id',
        'plan_expires_at',
        // Endereço
        'cep',
        'street',
        'number',
        'complement',
        'neighborhood',
        'city',
        'state',
        'address',
        // Redes Sociais
        'website',
        'facebook',
        'instagram',
        'twitter',
        'linkedin',
        'youtube',
        // Privacidade
        'show_email_public',
        'show_phone_public',
        'show_address_public'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'plan_expires_at' => 'datetime',
        'social_links' => 'array'
    ];

    public function plan()
    {
        return $this->belongsTo(\App\Models\Plan::class);
    }

    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

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

    // Relacionamentos de Conexão
    public function sentConnections()
    {
        return $this->hasMany(Connection::class, 'requester_id');
    }

    public function receivedConnections()
    {
        return $this->hasMany(Connection::class, 'requested_id');
    }

    // Verifica se já são conectados (aceito)
    public function isConnectedWith($userId)
    {
        return Connection::where(function ($q) use ($userId) {
            $q->where('requester_id', $this->id)->where('requested_id', $userId);
        })->orWhere(function ($q) use ($userId) {
            $q->where('requester_id', $userId)->where('requested_id', $this->id);
        })->where('status', 'accepted')->exists();
    }

    /**
     * Verifica se o usuário atual pode enviar mensagem para outro usuário
     * 
     * Regras:
     * - Admin/Superadmin podem mensagear qualquer um
     * - Membros só podem mensagear admin/superadmin se tiverem conexão com eles
     * - Membros podem responder se admin/superadmin mandou primeiro
     * - Membro para membro: obrigatória conexão ativa
     */
    public function canMessageUser($otherUser)
    {
        // Se o usuário logado é admin ou superadmin, pode mensagear qualquer um
        if ($this->isAdmin()) {
            return true;
        }

        // Se o outro usuário é admin/superadmin
        if ($otherUser->isAdmin()) {
            // Verifica se tem conexão
            if ($this->isConnectedWith($otherUser->id)) {
                return true;
            }

            // Verifica se o admin já iniciou conversa (enviou mensagem primeiro)
            $hasReceivedFromAdmin = \App\Models\Message::where('sender_id', $otherUser->id)
                ->where('receiver_id', $this->id)
                ->exists();

            return $hasReceivedFromAdmin;
        }

        // Membro para membro: obrigatória conexão ativa
        return $this->isConnectedWith($otherUser->id);
    }

    // Verifica se tem solicitação pendente (enviada ou recebida)
    public function hasPendingConnectionWith($userId)
    {
        return Connection::where(function ($q) use ($userId) {
            $q->where('requester_id', $this->id)->where('requested_id', $userId);
        })->orWhere(function ($q) use ($userId) {
            $q->where('requester_id', $userId)->where('requested_id', $this->id);
        })->where('status', 'pending')->first();
    }

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_user')->withPivot('role', 'joined_at');
    }

    public function receivedInteractions()
    {
        return $this->hasMany(Interaction::class, 'user_to_id');
    }

    public function ranking()
    {
        return $this->hasOne(Ranking::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function isProfileComplete(): bool
    {
        $requiredValues = [
            $this->phone,
            $this->occupation,
            $this->company,
            $this->bio,
            $this->city,
            $this->state,
            $this->photo,
        ];

        foreach ($requiredValues as $value) {
            if (blank($value)) {
                return false;
            }
        }

        return true;
    }
}
