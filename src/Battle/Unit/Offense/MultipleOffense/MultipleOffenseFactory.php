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
     * TODO min/max value
     *
     * @param array $data
     * @return MultipleOffenseInterface
     * @throws BattleException
     */
    public function create(array $data): MultipleOffenseInterface
    {
        return new MultipleOffense(
            self::floatOrDefault($data, 'damage', 1.0, MultipleOffenseException::INVALID_DAMAGE),
            self::floatOrDefault($data, 'attack_speed', 1.0, MultipleOffenseException::INVALID_ATTACK_SPEED),
            self::floatOrDefault($data, 'cast_speed', 1.0, MultipleOffenseException::INVALID_CAST_SPEED),
            self::floatOrDefault($data, 'accuracy', 1.0, MultipleOffenseException::INVALID_ACCURACY),
            self::floatOrDefault($data, 'magic_accuracy', 1.0, MultipleOffenseException::INVALID_MAGIC_ACCURACY),
            self::floatOrDefault($data, 'critical_chance', 1.0, MultipleOffenseException::INVALID_CRITICAL_CHANCE),
            self::floatOrDefault($data, 'critical_multiplier', 1.0, MultipleOffenseException::INVALID_CRITICAL_MULTIPLIER),
        );
    }
}
