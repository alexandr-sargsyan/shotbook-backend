<?php

namespace App\Http\Controllers;

use App\Models\Tutorial;
use Illuminate\Http\JsonResponse;

class TutorialController extends Controller
{
    /**
     * Получить список всех tutorials для селектора
     */
    public function index(): JsonResponse
    {
        $tutorials = Tutorial::select('id', 'label')
            ->orderBy('label')
            ->get();

        return response()->json([
            'data' => $tutorials,
        ]);
    }
}
