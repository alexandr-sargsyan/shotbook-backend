<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PlatformEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterVideoReferenceRequest;
use App\Http\Requests\StoreVideoReferenceRequest;
use App\Http\Requests\UpdateVideoReferenceRequest;
use App\Models\Tag;
use App\Models\Tutorial;
use App\Models\VideoReference;
use App\Services\PlatformNormalizationService;
use App\Services\PostgresSearchService;
use Illuminate\Http\JsonResponse;

class AdminVideoReferenceController extends Controller
{
    public function __construct(
        private PostgresSearchService $searchService,
        private PlatformNormalizationService $normalizationService
    ) {
    }

    /**
     * Display a listing of the resource (for admin).
     */
    public function index(FilterVideoReferenceRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $search = $validated['search'] ?? null;
        $perPage = $validated['per_page'] ?? 20;
        $page = $validated['page'] ?? 1;

        // Убираем служебные поля из фильтров
        $filters = $validated;
        unset($filters['search'], $filters['page'], $filters['per_page']);
        $filters = array_filter($filters, fn($value) => $value !== null);

        $results = $this->searchService->search($search, $filters, $perPage, $page);

        // Для админа загружаем все связи, включая служебные поля
        $items = $results->items();

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $results->currentPage(),
                'last_page' => $results->lastPage(),
                'per_page' => $results->perPage(),
                'total' => $results->total(),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreVideoReferenceRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Нормализация URL: определяем platform и извлекаем platform_video_id
        if (!empty($validated['source_url'])) {
            $normalized = $this->normalizationService->normalizeUrl($validated['source_url']);
            
            // Если нормализация успешна, используем её данные
            if ($normalized['normalized']) {
                $validated['platform'] = $normalized['platform'];
                $validated['platform_video_id'] = $normalized['platform_video_id'];
            } else {
                // Если не удалось нормализовать, пытаемся определить platform через enum
                if (empty($validated['platform'])) {
                    $platform = PlatformEnum::fromUrl($validated['source_url']);
                    if ($platform) {
                        $validated['platform'] = $platform->value;
                    }
                }
            }
        }

        // Обрабатываем теги: получаем или создаём теги по именам
        $tagIds = [];
        if (!empty($validated['tags']) && is_array($validated['tags'])) {
            foreach ($validated['tags'] as $tagName) {
                $tagName = trim($tagName);
                if (empty($tagName)) {
                    continue;
                }

                // Ищем существующий тег (case-insensitive)
                $tag = Tag::whereRaw('LOWER(name) = ?', [strtolower($tagName)])->first();

                if (!$tag) {
                    // Создаём новый тег
                    $tag = Tag::create(['name' => $tagName]);
                }

                $tagIds[] = $tag->id;
            }
        }

        // Убираем tags из validated, так как будем привязывать по ID
        unset($validated['tags']);

        // Создаём видео-референс
        $videoReference = VideoReference::create($validated);

        // Привязываем теги по ID
        if (!empty($tagIds)) {
            $videoReference->tags()->sync($tagIds);
        }

        // Обрабатываем tutorials: создаём новые или выбираем существующие
        if (!empty($validated['tutorials']) && is_array($validated['tutorials'])) {
            $tutorialSyncData = [];
            
            foreach ($validated['tutorials'] as $tutorialData) {
                $mode = $tutorialData['mode'] ?? 'new';
                $tutorialId = null;
                $pivotData = [];
                
                if ($mode === 'select') {
                    // Выбираем существующий tutorial
                    $tutorialId = $tutorialData['tutorial_id'];
                } else {
                    // Создаём новый tutorial
                    $tutorial = Tutorial::create([
                        'tutorial_url' => $tutorialData['tutorial_url'],
                        'label' => $tutorialData['label'],
                    ]);
                    $tutorialId = $tutorial->id;
                }
                
                // Добавляем pivot данные (start_sec, end_sec) если они переданы
                if (isset($tutorialData['start_sec'])) {
                    $pivotData['start_sec'] = $tutorialData['start_sec'];
                }
                if (isset($tutorialData['end_sec'])) {
                    $pivotData['end_sec'] = $tutorialData['end_sec'];
                }
                
                $tutorialSyncData[$tutorialId] = $pivotData;
            }
            
            // Синхронизируем tutorials с pivot данными
            if (!empty($tutorialSyncData)) {
                $videoReference->tutorials()->sync($tutorialSyncData);
            }
        }

        // Загружаем связи для ответа
        $videoReference->load(['category', 'tags', 'tutorials']);
        
        // Преобразуем tutorials, добавляя pivot данные на верхний уровень
        $videoReference->tutorials->transform(function ($tutorial) {
            if ($tutorial->pivot) {
                $tutorial->start_sec = $tutorial->pivot->start_sec;
                $tutorial->end_sec = $tutorial->pivot->end_sec;
            }
            return $tutorial;
        });

        return response()->json([
            'data' => $videoReference,
        ], 201);
    }

    /**
     * Display the specified resource (for admin).
     */
    public function show(string $id): JsonResponse
    {
        $videoReference = VideoReference::with(['category', 'tags', 'tutorials'])
            ->findOrFail($id);
        
        // Преобразуем tutorials, добавляя pivot данные на верхний уровень
        $videoReference->tutorials->transform(function ($tutorial) {
            if ($tutorial->pivot) {
                $tutorial->start_sec = $tutorial->pivot->start_sec;
                $tutorial->end_sec = $tutorial->pivot->end_sec;
            }
            return $tutorial;
        });

        return response()->json([
            'data' => $videoReference,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateVideoReferenceRequest $request, string $id): JsonResponse
    {
        $videoReference = VideoReference::findOrFail($id);
        $validated = $request->validated();

        // Если изменился source_url, перенормализовать
        if (isset($validated['source_url']) && 
            $validated['source_url'] !== $videoReference->source_url) {
            
            $normalized = $this->normalizationService->normalizeUrl($validated['source_url']);
            
            // Если нормализация успешна, используем её данные
            if ($normalized['normalized']) {
                $validated['platform'] = $normalized['platform'];
                $validated['platform_video_id'] = $normalized['platform_video_id'];
            } else {
                // Если не удалось нормализовать, пытаемся определить platform через enum
                if (empty($validated['platform'])) {
                    $platform = PlatformEnum::fromUrl($validated['source_url']);
                    if ($platform) {
                        $validated['platform'] = $platform->value;
                    }
                }
            }
        }

        // Обрабатываем теги: получаем или создаём теги по именам
        $tagIds = null;
        if (isset($validated['tags']) && is_array($validated['tags'])) {
            $tagIds = [];
            foreach ($validated['tags'] as $tagName) {
                $tagName = trim($tagName);
                if (empty($tagName)) {
                    continue;
                }

                // Ищем существующий тег (case-insensitive)
                $tag = Tag::whereRaw('LOWER(name) = ?', [strtolower($tagName)])->first();

                if (!$tag) {
                    // Создаём новый тег
                    $tag = Tag::create(['name' => $tagName]);
                }

                $tagIds[] = $tag->id;
            }
        }

        // Убираем tags из validated, так как будем привязывать по ID
        unset($validated['tags']);

        // Обновляем видео-референс
        $videoReference->update($validated);

        // Обновляем теги если переданы
        if ($tagIds !== null) {
            $videoReference->tags()->sync($tagIds);
        }

        // Обрабатываем tutorials: всегда синхронизируем, даже если пустой массив
        // Если tutorials переданы в запросе (включая пустой массив), синхронизируем
        if (array_key_exists('tutorials', $validated)) {
            $tutorialSyncData = [];
            
            if (is_array($validated['tutorials']) && !empty($validated['tutorials'])) {
                foreach ($validated['tutorials'] as $tutorialData) {
                    $mode = $tutorialData['mode'] ?? 'new';
                    $tutorialId = null;
                    $pivotData = [];
                    
                    if ($mode === 'select') {
                        // Выбираем существующий tutorial
                        $tutorialId = $tutorialData['tutorial_id'];
                    } else {
                        // Создаём новый tutorial
                        $tutorial = Tutorial::create([
                            'tutorial_url' => $tutorialData['tutorial_url'],
                            'label' => $tutorialData['label'],
                        ]);
                        $tutorialId = $tutorial->id;
                    }
                    
                    // Добавляем pivot данные (start_sec, end_sec) если они переданы
                    if (isset($tutorialData['start_sec'])) {
                        $pivotData['start_sec'] = $tutorialData['start_sec'];
                    }
                    if (isset($tutorialData['end_sec'])) {
                        $pivotData['end_sec'] = $tutorialData['end_sec'];
                    }
                    
                    $tutorialSyncData[$tutorialId] = $pivotData;
                }
            }
            
            // Синхронизируем tutorials (если пустой массив - удалит все связи)
            $videoReference->tutorials()->sync($tutorialSyncData);
        }

        // Загружаем связи для ответа
        $videoReference->load(['category', 'tags', 'tutorials']);
        
        // Преобразуем tutorials, добавляя pivot данные на верхний уровень
        $videoReference->tutorials->transform(function ($tutorial) {
            if ($tutorial->pivot) {
                $tutorial->start_sec = $tutorial->pivot->start_sec;
                $tutorial->end_sec = $tutorial->pivot->end_sec;
            }
            return $tutorial;
        });

        return response()->json([
            'data' => $videoReference,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $videoReference = VideoReference::findOrFail($id);
        $videoReference->delete();

        return response()->json([
            'message' => 'Video reference deleted successfully',
        ]);
    }
}
