<?php

namespace App\Http\Controllers;

use App\Models\Hook;
use Illuminate\Http\JsonResponse;

class HookController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $hooks = Hook::orderBy('name')->get();

        return response()->json([
            'data' => $hooks,
        ]);
    }
}
