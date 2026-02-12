<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignTutorialByTransitionTypeRequest;
use App\Http\Requests\StoreTutorialRequest;
use App\Http\Requests\UpdateTutorialRequest;
use App\Models\Tutorial;
use App\Models\TransitionType;
use App\Models\VideoReference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminTutorialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Tutorial::query();

        // Поиск по label или tutorial_url
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('label', 'ILIKE', "%{$searchTerm}%")
                  ->orWhere('tutorial_url', 'ILIKE', "%{$searchTerm}%");
            });
        }

        // Пагинация
        $perPage = $request->get('per_page', 20);
        $tutorials = $query->orderBy('label')
            ->paginate($perPage);

        return response()->json([
            'data' => $tutorials->items(),
            'meta' => [
                'current_page' => $tutorials->currentPage(),
                'last_page' => $tutorials->lastPage(),
                'per_page' => $tutorials->perPage(),
                'total' => $tutorials->total(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTutorialRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $tutorial = Tutorial::create($validated);

        return response()->json([
            'data' => $tutorial,
            'message' => 'Tutorial created successfully',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $tutorial = Tutorial::with('videoReferences')->findOrFail($id);

        return response()->json([
            'data' => $tutorial,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTutorialRequest $request, string $id): JsonResponse
    {
        $tutorial = Tutorial::findOrFail($id);
        $validated = $request->validated();

        $tutorial->update($validated);

        return response()->json([
            'data' => $tutorial,
            'message' => 'Tutorial updated successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $tutorial = Tutorial::findOrFail($id);
        $tutorial->delete();

        return response()->json([
            'message' => 'Tutorial deleted successfully',
        ]);
    }

    /**
     * Присвоить туториал всем видео с указанным Transition Type
     */
    public function assignByTransitionType(
        AssignTutorialByTransitionTypeRequest $request,
        string $id
    ): JsonResponse {
        $tutorial = Tutorial::findOrFail($id);
        $transitionTypeId = $request->validated()['transition_type_id'];

        // Получаем Transition Type
        $transitionType = TransitionType::findOrFail($transitionTypeId);

        // Получаем все видео с этим Transition Type
        $videoReferences = VideoReference::whereHas('transitionTypes', function ($query) use ($transitionTypeId) {
            $query->where('transition_types.id', $transitionTypeId);
        })->get();

        $assignedCount = 0;
        $skippedCount = 0;

        foreach ($videoReferences as $videoReference) {
            // Проверяем, есть ли уже этот туториал у видео
            $hasTutorial = $videoReference->tutorials()
                ->where('tutorials.id', $tutorial->id)
                ->exists();

            if (!$hasTutorial) {
                // Присваиваем туториал с null для start_sec и end_sec
                $videoReference->tutorials()->attach($tutorial->id, [
                    'start_sec' => null,
                    'end_sec' => null,
                ]);
                $assignedCount++;
            } else {
                $skippedCount++;
            }
        }

        return response()->json([
            'message' => 'Tutorial assigned successfully',
            'data' => [
                'tutorial_id' => $tutorial->id,
                'transition_type_id' => $transitionTypeId,
                'transition_type_name' => $transitionType->name,
                'total_videos' => $videoReferences->count(),
                'assigned_count' => $assignedCount,
                'skipped_count' => $skippedCount,
            ],
        ]);
    }
}
