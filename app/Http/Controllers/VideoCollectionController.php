<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCollectionRequest;
use App\Http\Requests\UpdateCollectionRequest;
use App\Models\VideoCollection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VideoCollectionController extends Controller
{
    /**
     * Список всех каталогов пользователя
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $collections = VideoCollection::where('user_id', $user->id)
            ->withCount('videoReferences')
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'data' => $collections,
        ]);
    }

    /**
     * Создать каталог
     */
    public function store(StoreCollectionRequest $request): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $collection = VideoCollection::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'is_default' => false,
        ]);

        return response()->json([
            'data' => $collection,
        ], 201);
    }

    /**
     * Детали каталога с видео
     */
    public function show(string $id, Request $request): JsonResponse
    {
        $user = $request->user();

        $collection = VideoCollection::where('user_id', $user->id)
            ->with(['videoReferences.categories', 'videoReferences.tags'])
            ->findOrFail($id);

        return response()->json([
            'data' => $collection,
        ]);
    }

    /**
     * Обновить каталог
     */
    public function update(UpdateCollectionRequest $request, string $id): JsonResponse
    {
        $user = $request->user();
        $collection = VideoCollection::where('user_id', $user->id)->findOrFail($id);

        // Проверяем, что это не дефолтный каталог
        if ($collection->is_default) {
            return response()->json([
                'message' => 'Дефолтный каталог "Избранное" нельзя переименовывать',
            ], 422);
        }

        $validated = $request->validated();
        $collection->update($validated);

        return response()->json([
            'data' => $collection,
        ]);
    }

    /**
     * Удалить каталог
     */
    public function destroy(string $id, Request $request): JsonResponse
    {
        $user = $request->user();
        $collection = VideoCollection::where('user_id', $user->id)->findOrFail($id);

        // Проверяем, что это не дефолтный каталог
        if ($collection->is_default) {
            return response()->json([
                'message' => 'Дефолтный каталог "Избранное" нельзя удалять',
            ], 422);
        }

        $collection->delete();

        return response()->json([
            'message' => 'Каталог успешно удален',
        ]);
    }
}
