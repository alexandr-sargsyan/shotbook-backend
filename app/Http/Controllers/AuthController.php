<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Models\VideoCollection;
use App\Services\EmailVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function __construct(
        private EmailVerificationService $verificationService
    ) {
    }

    /**
     * Регистрация пользователя
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Создаем пользователя
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => null, // Email не подтвержден
        ]);

        // Создаем дефолтный каталог "Favorites"
        VideoCollection::create([
            'user_id' => $user->id,
            'name' => 'Favorites',
            'is_default' => true,
        ]);

        // Отправляем код подтверждения
        $this->verificationService->sendVerificationCode($user->email);

        return response()->json([
            'message' => 'Пользователь успешно зарегистрирован. Код подтверждения отправлен на email.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
            ],
        ], 201);
    }

    /**
     * Вход пользователя
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        // Проверяем учетные данные
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Неверный email или пароль',
            ], 401);
        }

        $user = Auth::user();

        // Проверяем, подтвержден ли email
        if (!$user->isEmailVerified()) {
            return response()->json([
                'message' => 'Email не подтвержден. Пожалуйста, подтвердите email перед входом.',
            ], 403);
        }

        // Создаем токен доступа
        $token = $user->createToken('Personal Access Token')->accessToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Выход пользователя
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->token()->delete();

        return response()->json([
            'message' => 'Успешный выход',
        ]);
    }

    /**
     * Получить текущего пользователя
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'email_verified_at' => $request->user()->email_verified_at,
            ],
        ]);
    }
}
