<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoCollectionItem extends Model
{
    protected $fillable = [
        'collection_id',
        'video_reference_id',
    ];

    /**
     * Получить коллекцию
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(VideoCollection::class, 'collection_id');
    }

    /**
     * Получить видео
     */
    public function videoReference(): BelongsTo
    {
        return $this->belongsTo(VideoReference::class, 'video_reference_id');
    }
}
