<?php

namespace App\Http\Controllers;

use App\Models\VideoReference;
use App\Models\VideoReferenceLike;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    /**
     * Переключить лайк (добавить/убрать)
     */
    public function toggleLike(string $videoReferenceId, Request $request): JsonResponse
    {
        $user = $request->user();
        $videoReference = VideoReference::findOrFail($videoReferenceId);

        $like = VideoReferenceLike::where('user_id', $user->id)
            ->where('video_reference_id', $videoReferenceId)
            ->first();

        if ($like) {
            // Убираем лайк
            $like->delete();
            $liked = false;
        } else {
            // Добавляем лайк
            VideoReferenceLike::create([
                'user_id' => $user->id,
                'video_reference_id' => $videoReferenceId,
            ]);
            $liked = true;
        }

        $likesCount = $videoReference->likes()->count();

        return response()->json([
            'liked' => $liked,
            'likes_count' => $likesCount,
        ]);
    }

    /**
     * Проверить, лайкнул ли пользователь видео
     */
    public function checkLike(string $videoReferenceId, Request $request): JsonResponse
    {
        $user = $request->user();
        $videoReference = VideoReference::findOrFail($videoReferenceId);

        $liked = VideoReferenceLike::where('user_id', $user->id)
            ->where('video_reference_id', $videoReferenceId)
            ->exists();

        $likesCount = $videoReference->likes()->count();

        return response()->json([
            'liked' => $liked,
            'likes_count' => $likesCount,
        ]);
    }

    /**
     * Получить все лайки текущего пользователя
     */
    public function getUserLikes(Request $request): JsonResponse
    {
        $user = $request->user();

        $likes = VideoReferenceLike::where('user_id', $user->id)
            ->with('videoReference')
            ->get();

        return response()->json([
            'data' => $likes->map(function ($like) {
                return [
                    'id' => $like->id,
                    'video_reference_id' => $like->video_reference_id,
                    'video_reference' => $like->videoReference,
                    'created_at' => $like->created_at,
                ];
            }),
        ]);
    }
}
