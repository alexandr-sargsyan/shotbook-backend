<?php

namespace App\Services;

use App\Models\EmailVerificationCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class EmailVerificationService
{
    /**
     * Генерирует 6-значный код подтверждения
     */
    public function generateCode(string $email): string
    {
        // Помечаем все предыдущие неиспользованные коды как использованные
        EmailVerificationCode::where('email', $email)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->update(['verified_at' => now()]);

        // Генерируем новый 6-значный код
        $code = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Создаем запись с кодом
        EmailVerificationCode::create([
            'email' => $email,
            'code' => $code,
            'expires_at' => now()->addMinutes(15), // Код действителен 15 минут
        ]);

        return $code;
    }

    /**
     * Отправляет код подтверждения на email
     */
    public function sendVerificationCode(string $email): bool
    {
        try {
            $code = $this->generateCode($email);
            
            // Отправляем email через EmailService
            app(EmailService::class)->sendVerificationCode($email, $code);
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send verification code', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Проверяет код подтверждения
     */
    public function verifyCode(string $email, string $code): bool
    {
        $verificationCode = EmailVerificationCode::where('email', $email)
            ->where('code', $code)
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$verificationCode) {
            return false;
        }

        // Помечаем код как использованный
        $verificationCode->update(['verified_at' => now()]);

        // Подтверждаем email пользователя
        $this->markAsVerified($email);

        return true;
    }

    /**
     * Проверяет, истек ли код
     */
    public function isCodeExpired(EmailVerificationCode $code): bool
    {
        return $code->expires_at->isPast();
    }

    /**
     * Помечает email как подтвержденный
     */
    public function markAsVerified(string $email): void
    {
        User::where('email', $email)->update([
            'email_verified_at' => now(),
        ]);
    }
}

