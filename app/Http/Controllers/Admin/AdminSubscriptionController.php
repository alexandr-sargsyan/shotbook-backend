<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActivateSubscriptionRequest;
use App\Models\SubscriptionPlan;
use App\Models\User;
use App\Models\UserSubscription;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSubscriptionController extends Controller
{
    /**
     * Активировать подписку для пользователя
     */
    public function activate(ActivateSubscriptionRequest $request, string $userId): JsonResponse
    {
        $user = User::findOrFail($userId);
        $validated = $request->validated();

        $plan = SubscriptionPlan::findOrFail($validated['subscription_plan_id']);

        // Деактивируем все текущие активные подписки пользователя
        UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->update(['status' => 'cancelled']);

        // Создаем новую подписку
        $startsAt = now();
        $expiresAt = $startsAt->copy()->addDays($plan->duration_days);

        $subscription = UserSubscription::create([
            'user_id' => $user->id,
            'subscription_plan_id' => $plan->id,
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'status' => 'active',
            'activated_by' => $request->user()->id,
            'notes' => $validated['notes'] ?? null,
        ]);

        $subscription->load(['subscriptionPlan', 'activatedBy']);

        return response()->json([
            'data' => $subscription,
            'message' => 'Subscription activated successfully',
        ], 201);
    }

    /**
     * Деактивировать подписку пользователя
     */
    public function deactivate(Request $request, string $userId): JsonResponse
    {
        $user = User::findOrFail($userId);

        // Деактивируем все активные подписки
        $deactivated = UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->update([
                'status' => 'cancelled',
                'notes' => 'Deactivated by admin: ' . ($request->input('notes') ?? 'No reason provided'),
            ]);

        return response()->json([
            'message' => 'Subscription deactivated successfully',
            'deactivated_count' => $deactivated,
        ]);
    }

    /**
     * Получить историю подписок пользователя
     */
    public function history(string $userId): JsonResponse
    {
        $user = User::findOrFail($userId);

        $subscriptions = UserSubscription::where('user_id', $user->id)
            ->with(['subscriptionPlan', 'activatedBy'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $subscriptions,
        ]);
    }
}
