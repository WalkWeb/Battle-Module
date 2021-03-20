<?php

declare(strict_types=1);

namespace Battle;

use Throwable;

class Tools
{
    public static function rand(int $from, int $before): int
    {
        try {
            return random_int($from, $before);
        } catch (Throwable $e) {
            return rand($from, $before);
        }
    }
}
