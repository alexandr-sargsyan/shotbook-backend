<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hook extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * Получить все видео-референсы с этим хуком
     */
    public function videoReferences(): HasMany
    {
        return $this->hasMany(VideoReference::class);
    }
}
