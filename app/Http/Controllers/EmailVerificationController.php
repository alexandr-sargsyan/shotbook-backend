<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendCodeRequest;
use App\Http\Requests\VerifyCodeRequest;
use App\Models\User;
use App\Services\EmailVerificationService;
use Illuminate\Http\JsonResponse;

class EmailVerificationController extends Controller
{
    public function __construct(
        private EmailVerificationService $verificationService
    ) {
    }

    /**
     * Отправить код подтверждения на email
     */
    public function sendCode(SendCodeRequest $request): JsonResponse
    {
        $email = $request->validated()['email'];

        $success = $this->verificationService->sendVerificationCode($email);

        if (!$success) {
            return response()->json([
                'message' => 'Не удалось отправить код подтверждения',
            ], 500);
        }

        return response()->json([
            'message' => 'Код подтверждения отправлен на email',
        ]);
    }

    /**
     * Проверить код подтверждения
     */
    public function verifyCode(VerifyCodeRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $email = $validated['email'];
        $code = $validated['code'];

        $verified = $this->verificationService->verifyCode($email, $code);

        if (!$verified) {
            return response()->json([
                'message' => 'Неверный или истекший код подтверждения',
            ], 422);
        }

        // Находим пользователя и автоматически авторизуем
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Пользователь не найден',
            ], 404);
        }

        // Создаем токен доступа (как при логине)
        $token = $user->createToken('Personal Access Token')->accessToken;

        return response()->json([
            'message' => 'Email успешно подтвержден',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
            ],
        ]);
    }
}
