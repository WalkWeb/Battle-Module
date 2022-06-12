<?php

declare(strict_types=1);

namespace Battle\Unit\Defense;

use Battle\Traits\ValidationTrait;
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
    public static function create(array $data): Defense
    {
        self::int($data, 'block', DefenseException::INCORRECT_BLOCK);

        self::intMinMaxValue(
            $data['block'],
            DefenseInterface::MIN_BLOCK,
            DefenseInterface::MAX_BLOCK,
            DefenseException::INCORRECT_BLOCK_VALUE . DefenseInterface::MIN_BLOCK . '-' . DefenseInterface::MAX_BLOCK
        );

        self::int($data, 'defense', DefenseException::INCORRECT_DEFENSE);

        self::intMinMaxValue(
            $data['defense'],
            DefenseInterface::MIN_DEFENSE,
            DefenseInterface::MAX_DEFENSE,
            DefenseException::INCORRECT_DEFENSE_VALUE . DefenseInterface::MIN_DEFENSE . '-' . DefenseInterface::MAX_DEFENSE
        );

        return new Defense($data['defense'], $data['block']);
    }
}
