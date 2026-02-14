<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use App\Models\VideoCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->input('per_page', 20);
        $page = $request->input('page', 1);
        $search = $request->input('search');

        $query = User::with(['roles', 'activeSubscription.subscriptionPlan']);

        // Поиск по имени или email
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'ILIKE', "%{$search}%")
                  ->orWhere('email', 'ILIKE', "%{$search}%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')
            ->paginate($perPage, ['*'], 'page', $page);

        // Добавляем информацию о подписке
        $items = $users->map(function ($user) {
            $user->has_active_subscription = $user->hasActiveSubscription();
            $user->subscription_expires_at = $user->getSubscriptionExpiresAt();
            return $user;
        });

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ];

        // Если email_verified === true, устанавливаем email_verified_at
        if (isset($validated['email_verified']) && $validated['email_verified'] === true) {
            $userData['email_verified_at'] = now();
        }

        $user = User::create($userData);

        // Привязываем роли если указаны
        if (!empty($validated['role_ids'])) {
            $user->roles()->sync($validated['role_ids']);
        }

        // Создаем дефолтный каталог "Favorites"
        VideoCollection::create([
            'user_id' => $user->id,
            'name' => 'Favorites',
            'is_default' => true,
        ]);

        $user->load(['roles', 'activeSubscription.subscriptionPlan']);

        return response()->json([
            'data' => $user,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $user = User::with(['roles', 'subscriptions.subscriptionPlan', 'activeSubscription.subscriptionPlan'])
            ->findOrFail($id);

        $user->has_active_subscription = $user->hasActiveSubscription();
        $user->subscription_expires_at = $user->getSubscriptionExpiresAt();

        return response()->json([
            'data' => $user,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUserRequest $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $validated = $request->validated();

        // Обновляем пароль только если он передан
        if (isset($validated['password']) && !empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Обрабатываем email_verified
        if (isset($validated['email_verified'])) {
            if ($validated['email_verified'] === true) {
                $validated['email_verified_at'] = now();
            } else {
                $validated['email_verified_at'] = null;
            }
            unset($validated['email_verified']);
        }

        $user->update($validated);

        // Обновляем роли если указаны
        if (isset($validated['role_ids'])) {
            $user->roles()->sync($validated['role_ids']);
        }

        $user->load(['roles', 'activeSubscription.subscriptionPlan']);

        return response()->json([
            'data' => $user,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully',
        ]);
    }
}
