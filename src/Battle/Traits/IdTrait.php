<?php

declare(strict_types=1);

namespace Battle\Traits;

use Exception;

trait IdTrait
{
    /**
     * Генерация UUID не используется для лучшей производительности
     *
     * @param int $length
     * @return string
     * @throws Exception
     */
    protected static function generateId(int $length = 5): string
    {
        $limit = 100;
        $chars = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
        $numChars = strlen($chars);
        $string = '';
        for ($i = 1; $i < $length; $i++) {
            if (abs($i) > $limit) {
                break;
            }
            $string .= $chars[random_int(1, $numChars) - 1];
        }
        return $string;
    }
}
