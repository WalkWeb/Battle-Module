<?php

declare(strict_types=1);

namespace Battle\Traits;

use Battle\BattleException;
use Battle\Command\CommandInterface;
use Battle\Unit\UnitInterface;

trait ValidationTrait
{
    /**
     * @param array $data
     * @param string $filed
     * @param string $error
     * @return int
     * @throws BattleException
     */
    protected static function int(array $data, string $filed, string $error): int
    {
        if (!array_key_exists($filed, $data) || !is_int($data[$filed])) {
            throw new BattleException($error);
        }

        return $data[$filed];
    }

    /**
     * @param array $data
     * @param string $filed
     * @param string $error
     * @return int|float
     * @throws BattleException
     */
    protected static function intOrFloat(array $data, string $filed, string $error)
    {
        if (!array_key_exists($filed, $data) || (!is_float($data[$filed]) && !is_int($data[$filed]))) {
            throw new BattleException($error);
        }

        return $data[$filed];
    }

    /**
     * @param array $data
     * @param string $filed
     * @param string $error
     * @return string
     * @throws BattleException
     */
    protected static function string(array $data, string $filed, string $error): string
    {
        if (!array_key_exists($filed, $data) || !is_string($data[$filed])) {
            throw new BattleException($error);
        }

        return $data[$filed];
    }

    /**
     * @param array $data
     * @param string $filed
     * @param string $error
     * @return bool
     * @throws BattleException
     */
    protected static function bool(array $data, string $filed, string $error): bool
    {
        if (!array_key_exists($filed, $data) || !is_bool($data[$filed])) {
            throw new BattleException($error);
        }

        return $data[$filed];
    }

    /**
     * @param array $data
     * @param string $filed
     * @param string $error
     * @return array
     * @throws BattleException
     */
    protected static function array(array $data, string $filed, string $error): array
    {
        if (!array_key_exists($filed, $data) || !is_array($data[$filed])) {
            throw new BattleException($error);
        }

        return $data[$filed];
    }

    /**
     * @param int $value
     * @param int $min
     * @param int $max
     * @param string $error
     * @return int
     * @throws BattleException
     */
    protected static function intMinMaxValue(int $value, int $min, int $max, string $error): int
    {
        if ($value < $min || $value > $max) {
            throw new BattleException($error);
        }

        return $value;
    }

    /**
     * @param string $string
     * @param int $minLength
     * @param int $maxLength
     * @param string $error
     * @return string
     * @throws BattleException
     */
    protected static function stringMinMaxLength(string $string, int $minLength, int $maxLength, string $error): string
    {
        $length = mb_strlen($string);

        if ($length < $minLength || $length > $maxLength) {
            throw new BattleException($error);
        }

        return $string;
    }

    /**
     * @param array $data
     * @param string $filed
     * @param string $error
     * @return string|null
     * @throws BattleException
     */
    protected static function stringOrNull(array $data, string $filed, string $error): ?string
    {
        if (!array_key_exists($filed, $data)) {
            return null;
        }

        if (!is_string($data[$filed]) && !is_null($data[$filed])) {
            throw new BattleException($error);
        }

        return $data[$filed];
    }

    /**
     * @param array $data
     * @param string $filed
     * @param string $error
     * @return string
     * @throws BattleException
     */
    protected static function stringOrMissing(array $data, string $filed, string $error): string
    {
        if (!array_key_exists($filed, $data)) {
            return '';
        }

        if (!is_string($data[$filed])) {
            throw new BattleException($error);
        }

        return $data[$filed];
    }

    /**
     * @param array $data
     * @param string $filed
     * @param string $error
     * @return int
     * @throws BattleException
     */
    protected static function intOrMissing(array $data, string $filed, string $error): int
    {
        if (!array_key_exists($filed, $data)) {
            return 0;
        }

        if (!is_int($data[$filed])) {
            throw new BattleException($error);
        }

        return $data[$filed];
    }

    /**
     * Проверяет, что указанное значение соответствует одному из указанных значений (в массиве)
     *
     * @param $param
     * @param array $values
     * @param string $error
     * @return mixed
     * @throws BattleException
     */
    protected static function in($param, array $values, string $error)
    {
        if (in_array($param, $values, true)) {
            return $param;
        }

        throw new BattleException($error);
    }

    /**
     * @param array $data
     * @param string $filed
     * @param string $error
     * @return UnitInterface
     * @throws BattleException
     */
    protected static function unit(array $data, string $filed, string $error): UnitInterface
    {
        if (!array_key_exists($filed, $data) || !($data[$filed] instanceof UnitInterface)) {
            throw new BattleException($error);
        }

        return $data[$filed];
    }

    /**
     * @param array $data
     * @param string $filed
     * @param string $error
     * @return CommandInterface
     * @throws BattleException
     */
    protected static function command(array $data, string $filed, string $error): CommandInterface
    {
        if (!array_key_exists($filed, $data) || !($data[$filed] instanceof CommandInterface)) {
            throw new BattleException($error);
        }

        return $data[$filed];
    }
}
