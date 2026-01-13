<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class VideoCollection extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    /**
     * Получить пользователя
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить видео в коллекции
     */
    public function videoReferences(): BelongsToMany
    {
        return $this->belongsToMany(
            VideoReference::class,
            'video_collection_items',
            'collection_id',  // foreign key в pivot таблице для VideoCollection
            'video_reference_id'  // foreign key в pivot таблице для VideoReference
        )->withTimestamps();
    }

    /**
     * Проверить, является ли коллекция дефолтной
     */
    public function isDefault(): bool
    {
        return $this->is_default === true;
    }
}
