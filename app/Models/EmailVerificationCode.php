<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailVerificationCode extends Model
{
    protected $fillable = [
        'email',
        'code',
        'expires_at',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }

    /**
     * Проверить, истек ли код
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Проверить, использован ли код
     */
    public function isVerified(): bool
    {
        return $this->verified_at !== null;
    }

    /**
     * Проверить, действителен ли код
     */
    public function isValid(): bool
    {
        return !$this->isExpired() && !$this->isVerified();
    }
}
