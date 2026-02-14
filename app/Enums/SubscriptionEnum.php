<?php

namespace App\Enums;

enum SubscriptionEnum: int
{
    case FREE_VIDEO_LIMIT = 16;
    case MONTHLY_PRICE = 18;
    case YEARLY_PRICE = 120;
    case MONTHLY_DURATION_DAYS = 30;
    case YEARLY_DURATION_DAYS = 365;

    /**
     * Получить валюту по умолчанию
     */
    public static function currency(): string
    {
        return 'USD';
    }

    /**
     * Получить лимит видео для бесплатных пользователей
     */
    public static function freeVideoLimit(): int
    {
        return self::FREE_VIDEO_LIMIT->value;
    }
}
