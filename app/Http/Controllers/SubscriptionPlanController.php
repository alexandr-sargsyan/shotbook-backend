<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\Http\JsonResponse;

class SubscriptionPlanController extends Controller
{
    /**
     * Получить список доступных тарифных планов
     */
    public function index(): JsonResponse
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('duration_days', 'asc')
            ->get();

        return response()->json([
            'data' => $plans,
        ]);
    }
}
