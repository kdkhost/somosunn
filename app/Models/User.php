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
 * Este sistema é licensed, não vendido.
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

use App\Support\BrazilPhone;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Traits\HasRoles;
use App\Services\ProfilePhotoService;
use Illuminate\Support\Facades\Schema;
use App\Models\EventRegistration;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, Traits\HasFeatureAccess;

    /**
     * Override para usar notification customizada com MailTemplate.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\VerifyEmailNotification());
    }

    /**
     * Retorna o total de inscrições pagas/confirmadas.
     */
    public function getTotalTicketsCount(): int
    {
        if (array_key_exists('total_tickets_count', $this->attributes)) {
            return (int) $this->attributes['total_tickets_count'];
        }

        return $this->eventRegistrations()
            ->whereIn('status', [EventRegistration::STATUS_PAID, EventRegistration::STATUS_CONFIRMED])
            ->count();
    }

    /**
     * Retorna o total de inscrições onde check_in_at não é nulo.
     */
    public function getCheckedInTicketsCount(): int
    {
        if (array_key_exists('checked_in_tickets_count', $this->attributes)) {
            return (int) $this->attributes['checked_in_tickets_count'];
        }

        return $this->eventRegistrations()
            ->whereNotNull('check_in_at')
            ->count();
    }

    public function isAdmin()
    {
        return in_array($this->role, ['admin', 'superadmin']) || in_array($this->level, ['superadmin', 'sucesso']);
    }

    public function isSuperAdmin()
    {
        return $this->role === 'superadmin' || $this->level === 'superadmin';
    }

    public function isMarketingManager(): bool
    {
        return (int) Setting::get('platform_marketing_user_id', 0) === (int) $this->id;
    }

    public function hasPlatformFeeRoleExemption(): bool
    {
        return in_array($this->role, ['admin', 'superadmin'], true)
            || $this->isMarketingManager();
    }

    public function shouldChargePlatformFee(): bool
    {
        if ($this->hasPlatformFeeRoleExemption()) {
            return false;
        }

        if (!Schema::hasColumn($this->getTable(), 'platform_fee_enabled')) {
            return true;
        }

        return (bool) $this->platform_fee_enabled;
    }

    public function canManageReceivingPixKey(): bool
    {
        return in_array($this->role, ['admin', 'superadmin'], true)
            || $this->isMarketingManager();
    }

    public function canSellOnMarketplace(): bool
    {
        return $this->canAccessFeature('marketplace.sell')
            || $this->canAccessFeature('courses.create')
            || $this->canAccessFeature('events.create')
            || $this->canAccessFeature('mentorships.create');
    }

    public function canManageEventExhibitors(?Event $event = null): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        $hasExactPermissionOrFeature = function (string $permission): bool {
            $snakePermission = str_replace('.', '_', $permission);

            $hasRolePermission = $this->roles()
                ->whereHas('permissions', fn ($query) => $query->where('name', $permission))
                ->exists();

            return $hasRolePermission
                || $this->canAccessFeature($permission)
                || $this->canAccessFeature($snakePermission);
        };

        $hasGlobalAccess = $hasExactPermissionOrFeature('events.exhibitors.manage')
            || $this->canAccessFeature('admin.panel')
            || $this->canAccessFeature('admin_panel');

        if ($hasGlobalAccess) {
            return true;
        }

        $hasScopedAccess = collect([
            'events.edit',
            'events.create',
            'events.ticket.manage',
            'events.publish',
            'courses.edit',
            'courses.create',
            'mentorships.edit',
            'mentorships.create',
        ])->contains(fn (string $permission) => $hasExactPermissionOrFeature($permission));

        if (!$hasScopedAccess) {
            return false;
        }

        if (!$event) {
            return true;
        }

        return (int) $event->user_id === (int) $this->id;
    }

    public function canAccessInstructorArea(): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        return $this->canSellOnMarketplace()
            || $this->canManageEventExhibitors()
            || $this->canAccessFeature('courses_access')
            || $this->canAccessFeature('mentorships_access')
            || $this->canAccessFeature('events_access')
            || $this->canAccessFeature('certificates_access');
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
        'doc',
        'gender',
        'birth_date',
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
        'avatar',
        'pix_key',
        'platform_fee_enabled',
        // Sistema de indicação e aniversário
        'referral_code',
        'referred_by',
        'birth_date',
        'lgpd_accepted_at',
        'lgpd_version',
        'lgpd_accept_ip',
        'lgpd_accept_user_agent',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'plan_expires_at' => 'datetime',
        'extra_features' => 'array',
        'social_links' => 'array',
        'hide_profile' => 'boolean',
        'birth_date' => 'date',
        'lgpd_accepted_at' => 'datetime',
        'platform_fee_enabled' => 'boolean',
    ];

    protected $appends = ['profile_photo_url'];

    public function getPhoneAttribute($value): ?string
    {
        return BrazilPhone::format($value);
    }

    public function setPhoneAttribute($value): void
    {
        $this->attributes['phone'] = BrazilPhone::normalize($value);
    }

    /**
     * Auto-gera um código de referência único ao criar o usuário.
     */
    protected static function booted(): void
    {
        static::creating(function (self $user) {
            if (!Schema::hasColumn($user->getTable(), 'referral_code')) {
                return;
            }

            if (empty($user->referral_code)) {
                for ($attempt = 0; $attempt < 20; $attempt++) {
                    $code = 'UNN' . strtoupper(\Illuminate\Support\Str::random(7));
                    if (!self::where('referral_code', $code)->exists()) {
                        $user->referral_code = $code;

                        return;
                    }
                }

                throw new \RuntimeException('Nao foi possivel gerar um codigo de indicacao unico.');
            }
        });
    }

    public function plan()
    {
        return $this->belongsTo(\App\Models\Plan::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(\App\Models\Subscription::class);
    }

    public function gatewayAccounts()
    {
        return $this->hasMany(\App\Models\GatewayAccount::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function eventRegistrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function isConnectedWith($userId): bool
    {
        $targetUserId = (int) $userId;

        if (!$this->exists || $targetUserId <= 0) {
            return false;
        }

        if ((int) $this->id === $targetUserId) {
            return true;
        }

        return Connection::query()
            ->where('status', 'accepted')
            ->where(function ($query) use ($targetUserId) {
                $query->where(function ($accepted) use ($targetUserId) {
                    $accepted->where('requester_id', $this->id)
                        ->where('requested_id', $targetUserId);
                })->orWhere(function ($accepted) use ($targetUserId) {
                    $accepted->where('requester_id', $targetUserId)
                        ->where('requested_id', $this->id);
                });
            })
            ->exists();
    }

    public function hasPendingConnectionWith($userId)
    {
        $targetUserId = (int) $userId;

        return Connection::where('status', 'pending')
            ->where(function ($query) use ($targetUserId) {
                $query->where(function ($pending) use ($targetUserId) {
                    $pending->where('requester_id', $this->id)
                        ->where('requested_id', $targetUserId);
                })->orWhere(function ($pending) use ($targetUserId) {
                    $pending->where('requester_id', $targetUserId)
                        ->where('requested_id', $this->id);
                });
            })
            ->first();
    }

    public function canMessageUser($userOrId): bool
    {
        $targetId = ($userOrId instanceof \App\Models\User) ? $userOrId->id : $userOrId;

        // Allowed if connected or if they are the same person (self)
        if ((int) $this->id === (int) $targetId) {
            return true;
        }

        return $this->isConnectedWith($targetId);
    }

    public function courses()
    {
        try {
            if (Schema::hasColumn((new Course())->getTable(), 'user_id')) {
                return $this->hasMany(Course::class, 'user_id');
            }
        } catch (\Throwable $e) {
            // Fallback to legacy column below.
        }

        return $this->hasMany(Course::class, 'created_by');
    }

    public function createdCourses()
    {
        return $this->courses();
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function sellerStore()
    {
        return $this->hasOne(SellerStore::class);
    }

    public function sellerProducts()
    {
        return $this->hasMany(SellerProduct::class);
    }

    public function mentorships()
    {
        return $this->hasMany(Mentorship::class, 'mentor_id');
    }

    public function events()
    {
        return $this->hasMany(Event::class, 'user_id');
    }

    public function notifications()
    {
        return $this->morphMany(Notification::class, 'notifiable')->latest();
    }

    public function unreadNotifications()
    {
        return $this->morphMany(Notification::class, 'notifiable')->whereNull('read_at')->latest();
    }

    public function readNotifications()
    {
        return $this->morphMany(Notification::class, 'notifiable')->whereNotNull('read_at')->latest();
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

    public function conversations()
    {
        return $this->belongsToMany(Conversation::class)->withPivot('role', 'joined_at')->withTimestamps();
    }

    /**
     * Envia notificação de reset de senha em português.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new \App\Notifications\ResetPasswordNotification($token));
    }

    /**
     * Accessor para o score de reputacao do membro.
     * Retorna o score cacheado/banco sem disparar recalculo.
     */
    public function getReputationScoreAttribute(): int
    {
        return app(\App\Services\ReputationService::class)->getScore($this->id)['score'];
    }
}
