<?php

declare(strict_types=1);

namespace Battle\Unit\Defense;

use Battle\Traits\ValidationTrait;
use Battle\Unit\UnitInterface;
use Exception;

class DefenseFactory
{
    use ValidationTrait;

    /**
     * Создает Defense на основе массива с данными
     *
     * @param array $data
     * @return Defense
     * @throws Exception
     */
    public function create(array $data): Defense
    {
        self::existAndInt($data, 'block', DefenseException::INCORRECT_BLOCK);

        self::intMinMaxValue(
            $data['block'],
            UnitInterface::MIN_BLOCK,
            UnitInterface::MAX_BLOCK,
            DefenseException::INCORRECT_BLOCK_VALUE . UnitInterface::MIN_BLOCK . '-' . UnitInterface::MAX_BLOCK
        );

        self::existAndInt($data, 'defense', DefenseException::INCORRECT_DEFENSE);

        self::intMinValue(
            $data['defense'],
            UnitInterface::MIN_DEFENSE,
            DefenseException::INCORRECT_DEFENSE_VALUE . UnitInterface::MIN_DEFENSE
        );

        return new Defense($data['defense'], $data['block']);
    }
}
