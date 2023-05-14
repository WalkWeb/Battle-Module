<?php

declare(strict_types=1);

namespace Battle\Unit\Offense\MultipleOffense;

use Battle\Traits\ValidationTrait;
use Battle\Unit\Offense\OffenseInterface;
use Exception;

class MultipleOffenseFactory
{
    use ValidationTrait;

    /**
     * Создает объект MultipleOffense на основе массива с данными
     *
     * @param array $data
     * @return MultipleOffenseInterface
     * @throws Exception
     */
    public static function create(array $data): MultipleOffenseInterface
    {
        if (count($data) === 0) {
            throw new MultipleOffenseException(MultipleOffenseException::EMPTY_DATA);
        }

        $damage = self::floatOrDefault($data, 'damage', 1.0, MultipleOffenseException::INVALID_DAMAGE);
        $speed = self::floatOrDefault($data, 'speed', 1.0, MultipleOffenseException::INVALID_SPEED);
        $accuracy = self::floatOrDefault($data, 'accuracy', 1.0, MultipleOffenseException::INVALID_ACCURACY);
        $criticalChance = self::floatOrDefault($data, 'critical_chance', 1.0, MultipleOffenseException::INVALID_CRITICAL_CHANCE);
        $criticalMultiplier = self::floatOrDefault($data, 'critical_multiplier', 1.0, MultipleOffenseException::INVALID_CRITICAL_MULTIPLIER);
        $vampirism = self::intOfDefault($data, 'vampirism', 0, MultipleOffenseException::INVALID_VAMPIRISM);
        $blockIgnoring = self::intOfDefault($data, 'block_ignoring', 0, MultipleOffenseException::INVALID_BLOCK_IGNORING);
        $damageConvertTo = self::stringOrDefault($data, 'damage_convert', '', MultipleOffenseException::INVALID_CRITICAL_DAMAGE_CONVERT);

        self::floatMinMaxValue(
            $damage,
            MultipleOffenseInterface::MIN_MULTIPLIER,
            MultipleOffenseInterface::MAX_MULTIPLIER,
            MultipleOffenseException::INVALID_DAMAGE_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER
        );

        self::floatMinMaxValue(
            $speed,
            MultipleOffenseInterface::MIN_MULTIPLIER,
            MultipleOffenseInterface::MAX_MULTIPLIER,
            MultipleOffenseException::INVALID_SPEED_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER
        );

        self::floatMinMaxValue(
            $accuracy,
            MultipleOffenseInterface::MIN_MULTIPLIER,
            MultipleOffenseInterface::MAX_MULTIPLIER,
            MultipleOffenseException::INVALID_ACCURACY_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER
        );

        self::floatMinMaxValue(
            $criticalChance,
            MultipleOffenseInterface::MIN_MULTIPLIER,
            MultipleOffenseInterface::MAX_MULTIPLIER,
            MultipleOffenseException::INVALID_CRITICAL_CHANCE_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER
        );

        self::floatMinMaxValue(
            $criticalMultiplier,
            MultipleOffenseInterface::MIN_MULTIPLIER,
            MultipleOffenseInterface::MAX_MULTIPLIER,
            MultipleOffenseException::INVALID_CRITICAL_MULTIPLIER_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER
        );

        self::intMinMaxValue(
            $vampirism,
            OffenseInterface::MIN_VAMPIRE,
            OffenseInterface::MAX_VAMPIRE,
            MultipleOffenseException::INVALID_VAMPIRISM_VALUE . OffenseInterface::MIN_VAMPIRE . '-' . OffenseInterface::MAX_VAMPIRE
        );

        self::intMinMaxValue(
            $blockIgnoring,
            OffenseInterface::MIN_BLOCK_IGNORING,
            OffenseInterface::MAX_BLOCK_IGNORING,
            MultipleOffenseException::INVALID_BLOCK_IGNORING_VALUE . OffenseInterface::MIN_BLOCK_IGNORING . '-' . OffenseInterface::MAX_BLOCK_IGNORING
        );

        return new MultipleOffense(
            $damage,
            $speed,
            $accuracy,
            $criticalChance,
            $criticalMultiplier,
            $vampirism,
            $blockIgnoring,
            $damageConvertTo
        );
    }
}
