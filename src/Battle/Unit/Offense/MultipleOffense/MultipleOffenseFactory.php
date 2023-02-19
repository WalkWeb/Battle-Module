<?php

declare(strict_types=1);

namespace Battle\Unit\Offense\MultipleOffense;

use Battle\BattleException;
use Battle\Traits\ValidationTrait;

class MultipleOffenseFactory
{
    use ValidationTrait;

    /**
     * Создает объект MultipleOffense на основе массива с данными
     *
     * TODO Проверка на полное отсутствие параметров - в этом случае не нужно указывать MultipleOffense вовсе
     *
     * @param array $data
     * @return MultipleOffenseInterface
     * @throws BattleException
     */
    public function create(array $data): MultipleOffenseInterface
    {
        $damage = self::floatOrDefault($data, 'damage', 1.0, MultipleOffenseException::INVALID_DAMAGE);
        $attackSpeed = self::floatOrDefault($data, 'attack_speed', 1.0, MultipleOffenseException::INVALID_ATTACK_SPEED);
        $castSpeed = self::floatOrDefault($data, 'cast_speed', 1.0, MultipleOffenseException::INVALID_CAST_SPEED);
        $accuracy = self::floatOrDefault($data, 'accuracy', 1.0, MultipleOffenseException::INVALID_ACCURACY);
        $magicAccuracy = self::floatOrDefault($data, 'magic_accuracy', 1.0, MultipleOffenseException::INVALID_MAGIC_ACCURACY);
        $criticalChance = self::floatOrDefault($data, 'critical_chance', 1.0, MultipleOffenseException::INVALID_CRITICAL_CHANCE);
        $criticalMultiplier = self::floatOrDefault($data, 'critical_multiplier', 1.0, MultipleOffenseException::INVALID_CRITICAL_MULTIPLIER);

        self::floatMinMaxValue(
            $damage,
            MultipleOffenseInterface::MIN_MULTIPLIER,
            MultipleOffenseInterface::MAX_MULTIPLIER,
            MultipleOffenseException::INVALID_DAMAGE_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER
        );

        self::floatMinMaxValue(
            $attackSpeed,
            MultipleOffenseInterface::MIN_MULTIPLIER,
            MultipleOffenseInterface::MAX_MULTIPLIER,
            MultipleOffenseException::INVALID_ATTACK_SPEED_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER
        );

        self::floatMinMaxValue(
            $castSpeed,
            MultipleOffenseInterface::MIN_MULTIPLIER,
            MultipleOffenseInterface::MAX_MULTIPLIER,
            MultipleOffenseException::INVALID_CAST_SPEED_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER
        );

        self::floatMinMaxValue(
            $accuracy,
            MultipleOffenseInterface::MIN_MULTIPLIER,
            MultipleOffenseInterface::MAX_MULTIPLIER,
            MultipleOffenseException::INVALID_ACCURACY_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER
        );

        self::floatMinMaxValue(
            $magicAccuracy,
            MultipleOffenseInterface::MIN_MULTIPLIER,
            MultipleOffenseInterface::MAX_MULTIPLIER,
            MultipleOffenseException::INVALID_MAGIC_ACCURACY_VALUE . MultipleOffenseInterface::MIN_MULTIPLIER . '-' . MultipleOffenseInterface::MAX_MULTIPLIER
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
            $attackSpeed,
            $castSpeed,
            $accuracy,
            $magicAccuracy,
            $criticalChance,
            $criticalMultiplier,
        );
    }
}
