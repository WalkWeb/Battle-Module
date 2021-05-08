<?php

declare(strict_types=1);

namespace Battle\Traits;

use Exception;

trait IdTrait
{
    /**
     * Генерация UUID не используется для лучшей производительности
     *
     * @param int|null $length
     * @return string
     * @throws Exception
     */
    protected static function generateId(?int $length = 5): string
    {
        $chars = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $numChars = strlen($chars);
        $string = '';
        for ($i = 0; $i < $length; $i++) {
            $string .= $chars[random_int(1, $numChars) - 1];
        }
        return $string;
    }
}
