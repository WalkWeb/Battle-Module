<?php

declare(strict_types=1);

namespace Battle\Unit\Offense\MultipleOffense;

use Battle\Traits\ValidationTrait;
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
    public function create(array $data): MultipleOffenseInterface
    {
        if (count($data) === 0) {
            throw new MultipleOffenseException(MultipleOffenseException::EMPTY_DATA);
        }

        $damage = self::floatOrDefault($data, 'damage', 1.0, MultipleOffenseException::INVALID_DAMAGE);
        $speed = self::floatOrDefault($data, 'speed', 1.0, MultipleOffenseException::INVALID_SPEED);
        $accuracy = self::floatOrDefault($data, 'accuracy', 1.0, MultipleOffenseException::INVALID_ACCURACY);
        $criticalChance = self::floatOrDefault($data, 'critical_chance', 1.0, MultipleOffenseException::INVALID_CRITICAL_CHANCE);
        $criticalMultiplier = self::floatOrDefault($data, 'critical_multiplier', 1.0, MultipleOffenseException::INVALID_CRITICAL_MULTIPLIER);

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

        return new MultipleOffense(
            $damage,
            $speed,
            $accuracy,
            $criticalChance,
            $criticalMultiplier,
        );
    }
}
