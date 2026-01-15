<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddVideoRequest;
use App\Models\VideoCollection;
use App\Models\VideoCollectionItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VideoCollectionItemController extends Controller
{
    /**
     * Список видео в каталоге
     */
    public function index(string $collectionId, Request $request): JsonResponse
    {
        $user = $request->user();

        $collection = VideoCollection::where('user_id', $user->id)->findOrFail($collectionId);

        $videos = $collection->videoReferences()
            ->with(['categories', 'tags'])
            ->select('video_references.*')
            ->get();

        return response()->json([
            'data' => $videos,
        ]);
    }

    /**
     * Добавить видео в каталог
     */
    public function store(AddVideoRequest $request, string $collectionId): JsonResponse
    {
        $user = $request->user();
        $collection = VideoCollection::where('user_id', $user->id)->findOrFail($collectionId);

        $validated = $request->validated();
        $videoReferenceId = $validated['video_reference_id'];

        // Проверяем, не добавлено ли уже видео в этот каталог
        $existing = VideoCollectionItem::where('collection_id', $collectionId)
            ->where('video_reference_id', $videoReferenceId)
            ->exists();

        if ($existing) {
            return response()->json([
                'message' => 'Видео уже добавлено в этот каталог',
            ], 422);
        }

        VideoCollectionItem::create([
            'collection_id' => $collectionId,
            'video_reference_id' => $videoReferenceId,
        ]);

        return response()->json([
            'message' => 'Видео успешно добавлено в каталог',
        ], 201);
    }

    /**
     * Удалить видео из каталога
     */
    public function destroy(string $collectionId, string $videoReferenceId, Request $request): JsonResponse
    {
        $user = $request->user();
        $collection = VideoCollection::where('user_id', $user->id)->findOrFail($collectionId);

        $item = VideoCollectionItem::where('collection_id', $collectionId)
            ->where('video_reference_id', $videoReferenceId)
            ->firstOrFail();

        $item->delete();

        return response()->json([
            'message' => 'Видео успешно удалено из каталога',
        ]);
    }

    /**
     * Проверить, сохранено ли видео в каталогах пользователя
     */
    public function checkSaved(string $videoReferenceId, Request $request): JsonResponse
    {
        $user = $request->user();

        $collectionsWithVideo = VideoCollection::where('user_id', $user->id)
            ->whereHas('videoReferences', function ($query) use ($videoReferenceId) {
                $query->where('video_references.id', $videoReferenceId);
            })
            ->pluck('id')
            ->toArray();

        return response()->json([
            'is_saved' => count($collectionsWithVideo) > 0,
            'collection_ids' => $collectionsWithVideo,
        ]);
    }
}
