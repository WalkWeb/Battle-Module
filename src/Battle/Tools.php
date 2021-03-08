<?php

declare(strict_types=1);

namespace Battle;

class Tools
{
    public static function rand(int $from, int $before): int
    {
        try {
            $int = random_int($from, $before);
        } catch (\Throwable $e) {
            $int = rand($from, $before);
        }

        return $int;
    }
}
