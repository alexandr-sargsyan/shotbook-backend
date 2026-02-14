<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\JsonResponse;

class AdminRoleController extends Controller
{
    /**
     * Получить список всех ролей
     */
    public function index(): JsonResponse
    {
        $roles = Role::orderBy('name')->get();

        return response()->json([
            'data' => $roles,
        ]);
    }
}
