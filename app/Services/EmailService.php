<?php

namespace App\Services;

use App\Mail\EmailVerificationMail;
use Illuminate\Support\Facades\Mail;

class EmailService
{
    /**
     * Отправляет код подтверждения на email
     */
    public function sendVerificationCode(string $email, string $code): void
    {
        Mail::to($email)->send(new EmailVerificationMail($code));
    }
}

