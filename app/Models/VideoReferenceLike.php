<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoReferenceLike extends Model
{
    protected $fillable = [
        'user_id',
        'video_reference_id',
    ];

    /**
     * Получить пользователя
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Получить видео
     */
    public function videoReference(): BelongsTo
    {
        return $this->belongsTo(VideoReference::class);
    }
}
