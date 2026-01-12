<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tutorial extends Model
{
    protected $fillable = [
        'tutorial_url',
        'label',
    ];

    /**
     * Получить видео-референсы, связанные с этим tutorial
     */
    public function videoReferences(): BelongsToMany
    {
        return $this->belongsToMany(VideoReference::class, 'tutorial_video_reference')
            ->withPivot('start_sec', 'end_sec')
            ->withTimestamps();
    }

    /**
     * Валидация: хотя бы одно из полей должно быть заполнено
     */
    public static function boot(): void
    {
        parent::boot();

        static::saving(function (Tutorial $tutorial) {
            $hasUrl = !empty($tutorial->tutorial_url);
            $hasLabel = !empty($tutorial->label);

            if (!$hasUrl && !$hasLabel) {
                throw new \InvalidArgumentException('Хотя бы одно из полей должно быть заполнено: tutorial_url ИЛИ label');
            }
        });
    }
}
