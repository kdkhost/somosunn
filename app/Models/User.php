<?php
/**
 * =============================================================================
 * AVISO LEGAL DE DIREITOS AUTORAIS E PROPRIEDADE INTELECTUAL
 * =============================================================================
 *
 * © 2026 Marcelo Brad - Todos os direitos reservados.
 *
 * AUTOR:
 * marcelo-brad rj
 *
 * CONTATO:
 * Tel: +55 21 98132-5441
 * Email: contato@kdkhost.com.br
 * Telegram: @MARCELO_BRAD
 * Instagram: @marcelobradrj
 * WhatsApp: +55 21 98132-5441
 *
 * -----------------------------------------------------------------------------
 * DIREITOS AUTORAIS:
 * Este software, incluindo seu código-fonte, estrutura, banco de dados,
 * layout, funcionalidades, lógica de programação e documentação associada,
 * é protegido pelas leis brasileiras de direitos autorais (Lei nº 9.610/98)
 * e demais legislações internacionais aplicáveis.
 *
 * -----------------------------------------------------------------------------
 * PROPRIEDADE INTELECTUAL:
 * Todo o conteúdo deste sistema é de propriedade exclusiva do autor,
 * sendo proibida a reprodução total ou parcial, modificação,
 * engenharia reversa, redistribuição, sublicenciamento,
 * comercialização ou qualquer forma de exploração sem autorização
 * expressa e formal do titular dos direitos.
 *
 * -----------------------------------------------------------------------------
 * LICENÇA DE USO:
 * Este sistema é licenciado, não vendido.
 * O uso é restrito ao cliente contratante conforme contrato firmado.
 * É vedado o compartilhamento, revenda ou distribuição a terceiros
 * sem autorização prévia e documentada.
 *
 * -----------------------------------------------------------------------------
 * RESPONSABILIDADE:
 * Alterações realizadas por terceiros não autorizados anulam qualquer
 * responsabilidade do autor sobre falhas, vulnerabilidades ou danos
 * decorrentes do uso indevido do sistema.
 *
 * -----------------------------------------------------------------------------
 * SEGURANÇA E MONITORAMENTO:
 * Este software pode conter mecanismos de identificação,
 * rastreamento de licença e validação de integridade para
 * proteção contra uso não autorizado e pirataria.
 *
 * -----------------------------------------------------------------------------
 * PENALIDADES:
 * O uso indevido ou não autorizado poderá resultar em medidas legais
 * cabíveis nas esferas civil e criminal, incluindo indenizações por
 * perdas e danos.
 *
 * =============================================================================
 */

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Traits\HasRoles;
use App\Services\ProfilePhotoService;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, Traits\HasFeatureAccess;

    public function isAdmin()
    {
        return in_array($this->role, ['admin', 'superadmin']) || in_array($this->level, ['superadmin', 'sucesso']);
    }

    public function isSuperAdmin()
    {
        return $this->role === 'superadmin' || $this->level === 'superadmin';
    }

    public function canSellOnMarketplace(): bool
    {
        return $this->canAccessFeature('marketplace.sell')
            || $this->canAccessFeature('courses.create')
            || $this->canAccessFeature('events.create')
            || $this->canAccessFeature('mentorships.create');
    }

    public function hasPurchasedCourses(): bool
    {
        try {
            if ($this->isAdmin()) {
                return true;
            }

            $hasEnrollment = $this->enrollments()
                ->where('enrollable_type', \App\Models\Course::class)
                ->exists();

            if ($hasEnrollment) {
                return true;
            }

            return $this->orders()
                ->where('status', 'paid')
                ->whereHas('items', function ($query) {
                    $query->where('item_type', 'course');
                })
                ->exists();
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected $fillable = [
        'name',
        'email',
        'password',
        'gender',
        'cpf',
        'phone',
        'bio',
        'occupation',
        'company',
        'segment',
        'interests',
        'photo',
        'cover_photo',
        'role',
        'points',
        'theme_pref',
        'level',
        'plan_id',
        'plan_expires_at',
        'extra_features',
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
        'show_address_public',
        'hide_profile',
        // IDs Sociais
        'google_id',
        'facebook_id',
        'linkedin_id',
        'avatar'
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'plan_expires_at' => 'datetime',
        'extra_features' => 'array',
        'social_links' => 'array',
        'hide_profile' => 'boolean'
    ];

    protected $appends = ['profile_photo_url'];

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

    public function itemReviews()
    {
        return $this->hasMany(ItemReview::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
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

    public function getProfilePhotoUrlAttribute(): string
    {
        return app(ProfilePhotoService::class)->urlFor($this);
    }

    public function wishlist()
    {
        return $this->belongsToMany(Course::class, 'wishlists', 'user_id', 'course_id')->withTimestamps();
    }

    /**
     * Envia notificação de reset de senha em português.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }

    /**
     * Get the entity's notifications.
     */
    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable')->latest();
    }

    /**
     * Get the entity's unread notifications.
     */
    public function unreadNotifications()
    {
        return $this->morphMany(Notification::class, 'notifiable')->whereNull('read_at')->latest();
    }

    /**
     * Get the entity's read notifications.
     */
    public function readNotifications()
    {
        return $this->morphMany(Notification::class, 'notifiable')->whereNotNull('read_at')->latest();
    }
}
