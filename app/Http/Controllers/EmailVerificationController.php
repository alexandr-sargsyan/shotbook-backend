<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendCodeRequest;
use App\Http\Requests\VerifyCodeRequest;
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

        return response()->json([
            'message' => 'Email успешно подтвержден',
        ]);
    }
}
