<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hook;
use Illuminate\Http\JsonResponse;

class AdminHookController extends Controller
{
    /**
     * Display a listing of the resource (for admin).
     */
    public function index(): JsonResponse
    {
        $hooks = Hook::orderBy('name')->get();

        return response()->json([
            'data' => $hooks,
        ]);
    }
}
