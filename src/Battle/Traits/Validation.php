<?php

declare(strict_types=1);

namespace Battle\Traits;

use Battle\BattleException;

trait Validation
{
    /**
     * @param array $data
     * @param string $filed
     * @param string $error
     * @throws BattleException
     */
    protected static function existAndString(array $data, string $filed, string $error): void
    {
        if (!array_key_exists($filed, $data) || !is_string($data[$filed])) {
            throw new BattleException($error);
        }
    }

    /**
     * @param array $data
     * @param string $filed
     * @param string $error
     * @throws BattleException
     */
    protected static function existAndInt(array $data, string $filed, string $error): void
    {
        if (!array_key_exists($filed, $data) || !is_int($data[$filed])) {
            throw new BattleException($error);
        }
    }

    /**
     * @param int $value
     * @param int $min
     * @param int $max
     * @param string $error
     * @throws BattleException
     */
    protected static function intMinMaxValue(int $value, int $min, int $max, string $error): void
    {
        if ($value < $min || $value > $max) {
            throw new BattleException($error);
        }
    }

    /**
     * @param string $string
     * @param int $minLength
     * @param int $maxLength
     * @param $error
     * @throws BattleException
     */
    protected static function stringMinMaxLength(string $string, int $minLength, int $maxLength, $error): void
    {
        $length = mb_strlen($string);

        if ($length < $minLength || $length > $maxLength) {
            throw new BattleException($error);
        }
    }
}
