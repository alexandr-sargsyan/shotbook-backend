<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
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

    /**
     * Получить коды подтверждения email
     */
    public function emailVerificationCodes(): HasMany
    {
        return $this->hasMany(EmailVerificationCode::class, 'email', 'email');
    }

    /**
     * Получить лайки пользователя
     */
    public function likes(): HasMany
    {
        return $this->hasMany(VideoReferenceLike::class);
    }

    /**
     * Получить каталоги пользователя
     */
    public function collections(): HasMany
    {
        return $this->hasMany(VideoCollection::class);
    }

    /**
     * Получить дефолтный каталог пользователя
     */
    public function defaultCollection(): HasOne
    {
        return $this->hasOne(VideoCollection::class)->where('is_default', true);
    }

    /**
     * Проверить, подтвержден ли email
     */
    public function isEmailVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    /**
     * Получить роли пользователя
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role');
    }

    /**
     * Проверить, имеет ли пользователь указанную роль
     */
    public function hasRole(string $roleSlug): bool
    {
        return $this->roles()->where('slug', $roleSlug)->exists();
    }

    /**
     * Проверить, является ли пользователь администратором
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Получить все подписки пользователя
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Получить активную подписку пользователя
     */
    public function activeSubscription(): HasOne
    {
        return $this->hasOne(UserSubscription::class)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latest('expires_at');
    }

    /**
     * Проверить, имеет ли пользователь активную подписку
     */
    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription()->exists();
    }

    /**
     * Получить дату окончания активной подписки
     */
    public function getSubscriptionExpiresAt(): ?Carbon
    {
        $subscription = $this->activeSubscription;
        return $subscription ? $subscription->expires_at : null;
    }
}
